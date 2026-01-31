<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DonationSettings extends Settings
{
    public string $title;
    public string $description;
    public int $target_amount;
    public bool $is_active;

    public static function group(): string
    {
        return 'donation';
    }
}
