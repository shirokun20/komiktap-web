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
        $gateway = app(\App\Settings\PaymentGatewaySettings::class);

        $type = $request->query('type', 'all'); // 'all', 'order', 'donation'

        // Manual methods: hidden entirely when master toggle off; otherwise
        // filtered by usage_type and per-method is_active (default active).
        $methods = collect($settings->payment_methods)
            ->filter(function ($method) use ($type, $gateway) {
                if (! $gateway->manual_enabled) {
                    return false;
                }

                if (array_key_exists('is_active', $method) && ! $method['is_active']) {
                    return false;
                }

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

        // Both gateways coexist: frontend merges the synthetic QRIS Otomatis
        // card (when fansku_enabled) with the manual list above.
        return $this->success([
            'is_enabled' => $settings->is_enabled,
            'payment_methods' => $methods,
            'fansku_enabled' => (bool) $gateway->fansku_enabled,
        ]);
    }

    /**
     * GET /api/purchase-history — riwayat milik email login saja.
     * Status DB dipetakan ke kontrak mobile: approved→paid, rejected→failed.
     */
    public function purchaseHistory(Request $request)
    {
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1|max:10000',
            'perPage' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|in:pending,paid',
        ]);

        $email = $request->user()?->email;
        $query = \App\Models\Transaction::where('customer_contact', $email)->latest();

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status'] === 'paid' ? 'approved' : 'pending');
        }

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['perPage'] ?? 20);
        $total = (int) $query->count();
        $rows = $query->forPage($page, $perPage)->get();

        $items = $rows->map(function ($t) {
            $raw = $t->fansku_raw_response;
            if (! is_array($raw)) {
                $raw = [];
            }

            $mapped = $t->status === 'approved' ? 'paid' : ($t->status === 'rejected' ? 'failed' : 'pending');

            $item = [
                'transaction_code' => $t->code,
                'plan_name' => $t->plan_name,
                'device_quota' => (int) $t->device_quota,
                'duration_months' => (int) $t->duration_months,
                'amount' => $t->amount,
                'fee' => $raw['fee'] ?? $t->tripay_fee ?? 0,
                'total_amount' => $raw['total_amount'] ?? $t->amount,
                'payment_channel' => $t->fansku_support_id ? 'QRIS Otomatis' : ($t->payment_method ?? 'Manual'),
                'status' => $mapped,
                'created_at' => $t->created_at,
            ];

            if ($t->status === 'pending') {
                $item['qr_string'] = app(\App\Services\FanskuService::class)->extractQrString($raw);
            }

            return $item;
        })->values();

        return $this->success([
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => (int) ceil($total / $perPage),
            ],
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
