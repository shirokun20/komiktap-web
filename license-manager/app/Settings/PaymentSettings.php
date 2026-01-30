<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public bool $is_enabled;

    
    public array $payment_methods;

    public static function group(): string
    {
        return 'payment';
    }
}
