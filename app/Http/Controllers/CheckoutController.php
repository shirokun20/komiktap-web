<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

use App\Traits\ApiResponse;

class CheckoutController extends Controller
{
    use ApiResponse;

    public function store(Request $request, \App\Settings\PricingSettings $settings)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'plan_name' => 'required|string',
            'device_quota' => 'required|integer|min:1',
            'duration_months' => 'required|integer|min:1',
            // We authorize amount calculation on backend, but keep key for structure validation if needed
            'customer_contact' => 'required|string',
            'proof_digits' => 'required|string|max:5',
            'amount' => 'nullable|numeric|min:1000', // For Donation
            'voucher_code' => 'nullable|string',
            'payment_method' => 'required|string', // Name of the selected method
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $validated = $validator->validated();

        try {
            $planName = $validated['plan_name'];
            $devices = $validated['device_quota'];
            $duration = $validated['duration_months'];
            $finalAmount = 0;
            $customCode = null;
            
            // Payment Method Logic
            $paymentMethodName = $validated['payment_method'];
            $paymentSettings = app(\App\Settings\PaymentSettings::class);
            $selectedMethod = collect($paymentSettings->payment_methods)
                ->firstWhere('name', $paymentMethodName);

            if (!$selectedMethod) {
                // Fallback or Error? Let's verify instructions exist
                 throw new \Exception("Invalid payment method selected.");
            }

            // Format Payment Details for storage
            $details = "";
            if (!empty($selectedMethod['account_number'])) {
                $details .= "No: " . $selectedMethod['account_number'];
            }
            if (!empty($selectedMethod['account_holder'])) {
                $details .= " (" . $selectedMethod['account_holder'] . ")";
            }
            // Instructions not saved to DB anymore to keep invoice clean
            // if (!empty($selectedMethod['instructions'])) {
            //    $details .= "\nNotes: " . strip_tags($selectedMethod['instructions']);
            // }
            
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
                'proof_digits' => $validated['proof_digits'],
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

            return $this->success([
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->code,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Checkout Error: ' . $e->getMessage());
            return $this->error($e->getMessage(), 400); // Return 400 for bad request logic
        }
    }
}
