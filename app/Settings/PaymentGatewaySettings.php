<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentGatewaySettings extends Settings
{
    public bool $fansku_enabled;

    public bool $manual_enabled;

    public static function group(): string
    {
        return 'payment_gateway';
    }
}
