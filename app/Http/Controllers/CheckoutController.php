<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;
use App\Services\FanskuService;
use App\Services\TripayService;
use App\Traits\HoneypotTrait;
use Illuminate\Support\Facades\Log;

use App\Traits\ApiResponse;

class CheckoutController extends Controller
{
    use ApiResponse;
    use HoneypotTrait;

    public function store(Request $request, \App\Settings\PricingSettings $settings)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'plan_name' => 'required|string',
            'device_quota' => 'required|integer|min:1',
            'duration_months' => 'required|integer|min:1',
            // We authorize amount calculation on backend, but keep key for structure validation if needed
            'customer_contact' => 'required|string',
            'proof_digits' => 'nullable|string|max:5',
            'amount' => 'nullable|numeric|min:1000', // For Donation
            'voucher_code' => 'nullable|string',
            'payment_method' => 'required|string', // Name of the selected method
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 8.11 Honeypot check — reject silently if filled
        if ($this->isHoneypotFilled($request)) {
            return $this->success([
                'transaction_id'   => null,
                'transaction_code' => 'KURON-INV-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
                'message'          => 'Order received successfully!',
            ]);
        }

        $validated = $validator->validated();

        try {
            $planName = $validated['plan_name'];
            $devices = $validated['device_quota'];
            $duration = $validated['duration_months'];
            $finalAmount = 0;
            $customCode = null;
            
            // Payment Method Logic (Fansku QRIS primary — DB methods hidden temporarily)
            $paymentMethodName = $validated['payment_method'];
            $selectedMethod = null;
            $details = '';

            $isFanskuMethod = in_array($paymentMethodName, ['QRIS (Fansku)', 'FANSKU_QRIS', 'QRIS Otomatis'], true);

            if ($isFanskuMethod && config('fansku.is_enabled', false)) {
                // Bypass DB lookup — QRIS otomatis via Fansku
                $details = 'QRIS otomatis via Fansku';
            } else {
                $paymentSettings = app(\App\Settings\PaymentSettings::class);
                $selectedMethod = collect($paymentSettings->payment_methods)
                    ->firstWhere('name', $paymentMethodName);

                if (!$selectedMethod) {
                    // Fallback or Error? Let's verify instructions exist
                     throw new \Exception("Invalid payment method selected.");
                }

                // Format Payment Details for storage
                if (!empty($selectedMethod['account_number'])) {
                    $details .= "No: " . $selectedMethod['account_number'];
                }
                if (!empty($selectedMethod['account_holder'])) {
                    $details .= " (" . $selectedMethod['account_holder'] . ")";
                }
            }
            
            // Checks
            $isCampaign = \App\Models\DonationCampaign::where('title', $planName)->exists();

            if ($planName === 'Donasi' || $isCampaign) {
                // Donation Logic
                if (empty($validated['amount'])) {
                     throw new \Exception("Donation amount is required.");
                }
                $finalAmount = (int) $validated['amount'];
                
                // Generate KURON-PEDULI code
                $date = now()->format('Ymd');
                $random = strtoupper(\Illuminate\Support\Str::random(4));
                $customCode = "KURON-PEDULI-{$date}-{$random}";

            } elseif ($planName === 'Ketengan') {
                // Logic Ketengan (Matches Frontend but Secure)
                $basePrice = $settings->ketengan_base_price;
                
                $pricePerDevice = $basePrice;
                if ($devices > 1) {
                    $pricePerDevice = $basePrice * (1 - ($devices * $settings->device_discount_percentage));
                }

                $baseTotal = $pricePerDevice * $devices * $duration;

                // Duration Discount
                $discountPercent = 0;
                if ($duration >= 12) $discountPercent = $settings->discount_12_months;
                else if ($duration >= 6) $discountPercent = $settings->discount_6_months;
                else if ($duration >= 3) $discountPercent = $settings->discount_3_months;

                $discountValue = $baseTotal * $discountPercent;
                $finalAmount = $baseTotal - $discountValue;

            } else {
                // Standard Plans (Starter, Premium, Sultan)
                $plan = \App\Models\Plan::where('name', $planName)->first();
                
                if (!$plan) {
                    throw new \Exception("Plan not found: $planName");
                }

                // Standard plan price is fixed per month in DB
                // Assumption: Plan price in DB is monthly price
                $finalAmount = $plan->price * $duration;
            }

            // Voucher Logic
            $discountAmount = 0;
            $voucherCodeToUse = null;

            if (!empty($validated['voucher_code'])) {
                $voucher = \App\Models\Voucher::where('code', $validated['voucher_code'])->first();

                if (!$voucher || !$voucher->isValid()) {
                    throw new \Exception("Voucher code invalid or expired.");
                }

                $voucherCodeToUse = $voucher->code;

                if ($voucher->type === 'fixed') {
                    $discountAmount = $voucher->amount;
                } else {
                    $discountAmount = $finalAmount * ($voucher->amount / 100);
                }
                
                // Prevent negative amount
                if ($discountAmount > $finalAmount) {
                     $discountAmount = $finalAmount;
                }

                $finalAmount -= $discountAmount;
                
                // Increment Usage
                $voucher->increment('usage_count');
            }

            // Create Transaction with CALCULATED amount
            $transactionData = [
                'plan_name' => $planName,
                'device_quota' => $devices,
                'duration_months' => $duration,
                'amount' => max(0, $finalAmount), // Ensure non-negative
                'customer_contact' => $validated['customer_contact'],
                'proof_digits' => $validated['proof_digits'] ?? null,
                'status' => 'pending',
                'voucher_code' => $voucherCodeToUse,
                'discount_amount' => $discountAmount,
                'payment_method' => $paymentMethodName, // From validated
                'payment_details' => $details, // Formatted string
            ];

            if ($customCode) {
                $transactionData['code'] = $customCode;
            }

            $transaction = Transaction::create($transactionData);

            $transaction->refresh(); // Get the auto-generated code

            $message = 'Order received successfully!';
            if ($voucherCodeToUse) {
                $message .= " Voucher applied: Save IDR " . number_format($discountAmount);
            }

            $responseData = [
                'transaction_id'   => $transaction->id,
                'transaction_code' => $transaction->code,
                'message'          => $message,
            ];

            // Fansku QRIS (primary gateway) — before Tripay/manual fallback.
            // Conditions: flag on + email contact + QRIS active.
            if ($this->shouldUseFansku($transaction)) {
                try {
                    $fanskuData = $this->createFanskuSupport($transaction);
                    $responseData['fansku'] = $fanskuData;
                    $message .= ' Scan QRIS to complete payment.';
                } catch (\Exception $e) {
                    Log::error('Checkout: Fansku support creation failed, falling back', [
                        'transaction' => $transaction->code,
                        'error'       => $e->getMessage(),
                    ]);
                    // Don't throw — fall through to Tripay/manual below.
                }
            }

            // 4.1 TriPay integration — call after amount calculation if enabled
            // Skipped when Fansku already produced a QR.
            if (! isset($responseData['fansku']) && config('tripay.is_enabled', false)) {
                try {
                    $tripayData = $this->createTripayTransaction($transaction, $paymentMethodName, $request);
                    // 4.3 Return tripay pay_code / checkout_url in response
                    $responseData['tripay'] = $tripayData;
                    $message .= ' Payment created via TriPay.';
                } catch (\Exception $e) {
                    // 4.4 Backward compatibility: skip Tripay if API fails
                    Log::error('Checkout: TriPay transaction creation failed, falling back to manual', [
                        'transaction' => $transaction->code,
                        'error'       => $e->getMessage(),
                    ]);
                    // Don't throw — let the order proceed manually
                }
            }

            return $this->success($responseData);

        } catch (\Exception $e) {
            Log::error('Checkout Error: ' . $e->getMessage());
            return $this->error($e->getMessage(), 400); // Return 400 for bad request logic
        }
    }

    /**
     * Check whether this transaction qualifies for Fansku QRIS auto-payment.
     * Requires: flag on + email contact + QRIS active on Fansku side.
     */
    protected function shouldUseFansku(Transaction $transaction): bool
    {
        if (! config('fansku.is_enabled', false)) {
            return false;
        }

        if (! str_contains($transaction->customer_contact ?? '', '@')) {
            return false;
        }

        if ((int) $transaction->amount < 1) {
            return false;
        }

        try {
            return app(FanskuService::class)->isQrisActive();
        } catch (\Exception $e) {
            Log::warning('Checkout: Fansku QRIS check failed, using fallback', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create Fansku QRIS support and store identifiers on the transaction.
     * Local amount stays as revenue; total_amount (incl. fee) is shown on QR.
     */
    protected function createFanskuSupport(Transaction $transaction): array
    {
        $fansku = app(FanskuService::class);
        $contact = $transaction->customer_contact;

        $support = $fansku->createSupport([
            'name' => explode('@', $contact)[0],
            'email' => $contact,
            'message' => "KomikTap {$transaction->plan_name} ({$transaction->code})",
            'amount' => (int) $transaction->amount,
            'payment_method' => 'qris',
        ]);

        $transaction->update([
            'fansku_support_id' => $support['support_id'] ?? null,
            'fansku_status' => 'pending',
            'fansku_raw_response' => $support['raw'] ?? null,
            'payment_method' => 'QRIS (Fansku)',
        ]);

        return [
            'support_id' => $support['support_id'] ?? null,
            'qr_string' => $support['qr_string'] ?? null,
            'amount' => $support['amount'] ?? (int) $transaction->amount,
            'fee' => $support['fee'] ?? 0,
            'total_amount' => $support['total_amount'] ?? (int) $transaction->amount,
        ];
    }

    /**
     * 4.1 / 4.2 Create TriPay transaction and store reference in the transaction record.
     */
    protected function createTripayTransaction(Transaction $transaction, string $paymentMethodName, Request $request): array
    {
        $tripay = app(TripayService::class);

        $contact = $transaction->customer_contact;
        $isEmail = str_contains($contact, '@');

        $tripayData = $tripay->createTransaction([
            'method'         => $paymentMethodName,
            'merchant_ref'   => $transaction->code,
            'amount'         => (int) $transaction->amount,
            'customer_name'  => $isEmail ? explode('@', $contact)[0] : $contact,
            'customer_email' => $isEmail ? $contact : 'noreply@komiktap.info',
            'customer_phone' => $isEmail ? '08000000000' : $contact,
            'order_items'    => [
                [
                    'name'     => $transaction->plan_name,
                    'price'    => (int) $transaction->amount,
                    'quantity' => 1,
                ],
            ],
            'return_url' => url('/success/' . $transaction->code),
        ]);

        // 4.2 Store tripay reference and metadata
        $transaction->update([
            'tripay_reference'      => $tripayData['reference'] ?? null,
            'tripay_payment_method' => $tripayData['payment_method'] ?? $paymentMethodName,
            'tripay_expired_at'     => isset($tripayData['expired_time'])
                ? \Carbon\Carbon::createFromTimestamp($tripayData['expired_time'])
                : null,
            'tripay_raw_response'   => $tripayData,
        ]);

        return [
            'reference'    => $tripayData['reference'] ?? null,
            'pay_code'     => $tripayData['pay_code'] ?? null,
            'checkout_url' => $tripayData['checkout_url'] ?? null,
            'expired_time' => $tripayData['expired_time'] ?? null,
            'qr_string'    => $tripayData['qr_string'] ?? null,
        ];
    }
}
