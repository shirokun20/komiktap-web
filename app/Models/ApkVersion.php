<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApkVersion extends Model
{
    protected $fillable = [
        'version_code',
        'version_name',
        'file_path',
        'changelog',
        'is_active',
        'download_count',
        'min_android_version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'download_count' => 'integer',
    ];
}
