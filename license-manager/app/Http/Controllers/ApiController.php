<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Settings\PricingSettings;
use App\Traits\ApiResponse;

class ApiController extends Controller
{
    use ApiResponse;

    public function config(PricingSettings $settings)
    {
        return $this->success([
            'ketengan_base_price' => $settings->ketengan_base_price,
            'discount_3_months' => $settings->discount_3_months,
            'discount_6_months' => $settings->discount_6_months,
            'discount_12_months' => $settings->discount_12_months,
            'device_discount_percentage' => $settings->device_discount_percentage,
        ]);
    }

    public function plans()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        return $this->success($plans);
    }
}
