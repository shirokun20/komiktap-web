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

            // Create Transaction with CALCULATED amount
            $transactionData = [
                'plan_name' => $planName,
                'device_quota' => $devices,
                'duration_months' => $duration,
                'amount' => $finalAmount, // Secure Amount
                'customer_contact' => $validated['customer_contact'],
                'proof_digits' => $validated['proof_digits'],
                'status' => 'pending',
            ];

            if ($customCode) {
                $transactionData['code'] = $customCode;
            }

            $transaction = Transaction::create($transactionData);

            $transaction->refresh(); // Get the auto-generated code

            return $this->success([
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->code,
                'message' => 'Order received successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Checkout Error: ' . $e->getMessage());
            return $this->error('Failed to process order. ' . $e->getMessage(), 500);
        }
    }
}
