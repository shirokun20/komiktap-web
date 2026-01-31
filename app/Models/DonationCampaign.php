<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationCampaign extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'target_amount' => 'decimal:2',
    ];
}
