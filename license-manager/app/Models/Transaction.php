<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'customer_contact' => 'encrypted',
    ];

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
}
