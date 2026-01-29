<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PricingSettings extends Settings
{

    public float $ketengan_base_price;
    public float $discount_3_months;
    public float $discount_6_months;
    public float $discount_12_months;
    public float $device_discount_percentage;

    public static function group(): string
    {
        return 'pricing';
    }
}