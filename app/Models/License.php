<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class License extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        // 'customer_contact' => 'encrypted',
        'expires_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'max_devices', 'expires_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function devices()
    {
        return $this->hasMany(LicenseDevice::class);
    }
}
