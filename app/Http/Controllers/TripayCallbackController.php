<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\License;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TripayCallbackController extends Controller
{
    public function handle(Request $request, TripayService $tripay)
    {
        // 3.2 Validate callback signature
        $rawBody  = $request->getContent();
        $signature = $request->header('X-Callback-Signature', '');

        if (! $tripay->validateCallback($rawBody, $signature)) {
            Log::warning('TripayCallback: invalid signature', [
                'ip'        => $request->ip(),
                'signature' => $signature,
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();

        // 3.3 Lookup transaction by merchant_ref
        $merchantRef = $payload['merchant_ref'] ?? null;

        if (! $merchantRef) {
            return response()->json(['success' => false, 'message' => 'Missing merchant_ref'], 400);
        }

        $transaction = Transaction::where('code', $merchantRef)->first();

        if (! $transaction) {
            Log::warning('TripayCallback: unknown merchant_ref', ['merchant_ref' => $merchantRef]);
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        $tripayStatus = strtoupper($payload['status'] ?? '');

        // Store raw response always
        $transaction->tripay_raw_response = $payload;
        $transaction->tripay_status       = $tripayStatus;

        if ($tripayStatus === 'PAID') {
            // 3.4 PAID → approve + store fee/amount/paid_at
            $transaction->tripay_amount_received = $payload['total_amount'] ?? null;
            $transaction->tripay_fee             = $payload['total_fee'] ?? null;
            $transaction->tripay_paid_at         = isset($payload['paid_at'])
                ? \Carbon\Carbon::createFromTimestamp($payload['paid_at'])
                : now();

            // Only approve if still pending (idempotent)
            if ($transaction->status === 'pending') {
                // Generate license for non-donation orders
                $isDonation = $transaction->plan_name === 'Donasi' ||
                    \App\Models\DonationCampaign::where('title', $transaction->plan_name)->exists();

                if (! $isDonation) {
                    $random    = strtoupper(Str::random(12));
                    $formatted = implode('-', str_split($random, 4));
                    $key       = "KURON-{$formatted}";
                    $expiresAt = now()->addMonths($transaction->duration_months);

                    $license = License::create([
                        'key'              => $key,
                        'status'           => 'active',
                        'max_devices'      => $transaction->device_quota,
                        'expires_at'       => $expiresAt,
                        'customer_contact' => $transaction->customer_contact,
                    ]);

                    $transaction->license_id = $license->id;
                }

                $transaction->status = 'approved';
            }

            $transaction->save();

            // 3.6 Send email notification if contact is an email
            $this->sendEmailNotification($transaction);

        } elseif (in_array($tripayStatus, ['FAILED', 'EXPIRED'])) {
            // 3.5 FAILED/EXPIRED → reject
            if ($transaction->status === 'pending') {
                $transaction->status = 'rejected';
            }
            $transaction->save();
        } else {
            // Other statuses (UNPAID, REFUND, etc.) — just save the status
            $transaction->save();
        }

        // 3.8 Proper JSON response for TriPay retry mechanism
        return response()->json(['success' => true, 'message' => 'OK']);
    }

    /**
     * 3.6 Send email notification on PAID (only for email contacts).
     */
    protected function sendEmailNotification(Transaction $transaction): void
    {
        $contact = $transaction->customer_contact ?? '';

        // 6.3 Email detection: contains '@' → email, else → WA (skip)
        if (! str_contains($contact, '@')) {
            Log::info('TripayCallback: WA contact, skipping email notification', [
                'transaction' => $transaction->code,
            ]);
            return;
        }

        try {
            Mail::to($contact)->queue(new \App\Mail\TransactionInvoice($transaction));
        } catch (\Exception $e) {
            Log::error('TripayCallback: failed to queue email', [
                'transaction' => $transaction->code,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
