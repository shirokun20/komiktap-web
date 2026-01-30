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
            if (empty($transaction->code)) {
                $date = now()->format('Ymd');
                $random = strtoupper(\Illuminate\Support\Str::random(4));
                $transaction->update([
                    'code' => "KURON-INV-{$date}-{$random}",
                ]);
            }
        });
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
