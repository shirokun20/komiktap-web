<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Settings\PricingSettings;
use App\Traits\ApiResponse;

class ApiController extends Controller
{
    use ApiResponse;

    public function me(Request $request)
    {
        return $this->success($request->user());
    }

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

    public function faqs()
    {
        $faqs = \App\Models\Faq::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        return $this->success($faqs);
    }

    public function paymentMethods(Request $request, \App\Settings\PaymentSettings $settings)
    {
        $type = $request->query('type', 'all'); // 'all', 'order', 'donation'

        $methods = collect($settings->payment_methods)
            ->filter(function ($method) use ($type) {
                // If usage_type is not set, assume 'all' (backward compatibility)
                $usage = $method['usage_type'] ?? 'all';
                
                // If requesting 'all', return everything
                if ($type === 'all') return true;

                // If method is 'all', it appears everywhere
                if ($usage === 'all') return true;

                // strict match
                return $usage === $type;
            })
            ->values() // Reset keys
            ->map(function ($method) {
                if (!empty($method['instructions'])) {
                    $method['instructions'] = str($method['instructions'])->markdown();
                }
                return $method;
            });

        return $this->success([
            'is_enabled' => $settings->is_enabled,
            'payment_methods' => $methods
        ]);
    }

    public function checkVoucher(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'voucher_code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $code = $request->voucher_code;
        $amount = $request->amount;

        $voucher = \App\Models\Voucher::where('code', $code)->first();

        // Using isValid() check from Model
        if (!$voucher || !$voucher->isValid()) {
            return $this->error('Kode voucher tidak valid, kadaluarsa, atau sudah habis.', 400); 
        }

        $discount = 0;
        if ($voucher->type === 'fixed') {
            $discount = $voucher->amount;
        } else {
            $discount = $amount * ($voucher->amount / 100);
        }

        // Cap discount at amount
        if ($discount > $amount) $discount = $amount;

        return $this->success([
            'valid' => true,
            'code' => $voucher->code,
            'discount_amount' => $discount,
            'final_amount' => max(0, $amount - $discount),
            'message' => "Hemat IDR " . number_format($discount)
        ]);
    }
}
