<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $guarded = [];

    protected $casts = [
        'customer_contact' => 'encrypted',
        'expires_at' => 'datetime',
    ];
}
