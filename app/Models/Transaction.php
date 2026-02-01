<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Transaction extends Model
{
    use LogsActivity;

    protected $guarded = [];
    
    protected $casts = [
        // 'customer_contact' => 'encrypted', // Encryption disabled for searchability
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_proof', 'plan_name', 'amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted()
    {
        static::created(function ($transaction) {
            // Auto-generate code if empty
            if (empty($transaction->code)) {
                $date = now()->format('Ymd');
                $random = strtoupper(\Illuminate\Support\Str::random(4));
                $transaction->updateQuietly([ // Use updateQuietly to avoid recursion if we add updated events later
                    'code' => "KURON-INV-{$date}-{$random}",
                ]);
            }

            // Customer Intelligence Sync - REMOVED from created
            // Logic moved to updating when status becomes approved
        });

        static::updating(function ($transaction) {
            // Determine if this is a Donation
            $isDonation = $transaction->plan_name === 'Donasi' || 
                          \App\Models\DonationCampaign::where('title', $transaction->plan_name)->exists();

            // Only sync customer when status is APPROVED AND it is NOT a donation (Order Only)
            if (!$isDonation && $transaction->isDirty('status') && $transaction->status === 'approved') {
                 
                 // Create or Link Customer
                 if (!empty($transaction->customer_contact)) {
                    $customer = \App\Models\Customer::firstOrCreate(
                        ['contact' => $transaction->customer_contact],
                        ['name' => 'New Customer']
                    );
                    $transaction->customer_id = $customer->id;
                 }

                 // Ban Check
                 if ($transaction->customer && $transaction->customer->is_banned) {
                    $transaction->status = 'rejected';
                    \Filament\Notifications\Notification::make()
                        ->title('Banned Customer Detected')
                        ->body('This customer is banned. Transaction auto-rejected.')
                        ->danger()
                        ->send();
                }
            }
            
            // Also sync if contact changes WHILE approved (and not donation)
             if (!$isDonation && $transaction->status === 'approved' && $transaction->isDirty('customer_contact')) {
                 $customer = \App\Models\Customer::firstOrCreate(
                    ['contact' => $transaction->customer_contact]
                );
                $transaction->customer_id = $customer->id;
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
