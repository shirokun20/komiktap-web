<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Transaction;
use App\Services\FanskuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FanskuWebhookController extends Controller
{
    public function handle(Request $request, FanskuService $fansku)
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Fansku-Signature', '');

        if (! $fansku->validateWebhook($rawBody, $signature)) {
            Log::warning('FanskuWebhook: invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? $request->header('X-Fansku-Event', '');

        // Only donation.paid mutates; other events ack without mutation
        // so the sender does not retry pointlessly.
        if ($event !== 'donation.paid') {
            return response()->json(['ok' => true]);
        }

        // Test-mode webhooks must never mutate.
        if (! empty($payload['is_test'])) {
            Log::info('FanskuWebhook: test event ignored', [
                'support_id' => $payload['data']['support_id'] ?? null,
            ]);

            return response()->json(['ok' => true]);
        }

        $data = $payload['data'] ?? [];
        $supportId = $data['support_id'] ?? null;
        $code = $data['code'] ?? null;

        if (! $supportId && ! $code) {
            return response()->json(['error' => 'Missing support identifier'], 400);
        }

        // Primary key: support_id; fallback: code.
        $transaction = null;
        if ($supportId) {
            $transaction = Transaction::where('fansku_support_id', $supportId)->first();
        }
        if (! $transaction && $code) {
            $transaction = Transaction::where('fansku_code', $code)->first();
        }

        if (! $transaction) {
            Log::warning('FanskuWebhook: unknown support identifier', [
                'support_id' => $supportId,
                'code' => $code,
            ]);

            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Backfill code when matched by support_id.
        if ($code && empty($transaction->fansku_code)) {
            $transaction->fansku_code = $code;
        }

        $transaction->fansku_raw_response = $payload;
        $transaction->fansku_status = 'paid';
        $transaction->fansku_paid_at = isset($data['created_at'])
            ? \Carbon\Carbon::parse($data['created_at'])
            : now();

        // Idempotent: only approve from pending.
        if ($transaction->status === 'pending') {
            $isDonation = $transaction->plan_name === 'Donasi' ||
                \App\Models\DonationCampaign::where('title', $transaction->plan_name)->exists();

            if (! $isDonation) {
                $random = strtoupper(Str::random(12));
                $formatted = implode('-', str_split($random, 4));
                $key = "KURON-{$formatted}";
                $expiresAt = now()->addMonths($transaction->duration_months);

                $license = License::create([
                    'key' => $key,
                    'status' => 'active',
                    'max_devices' => $transaction->device_quota,
                    'expires_at' => $expiresAt,
                    'customer_contact' => $transaction->customer_contact,
                ]);

                $transaction->license_id = $license->id;
            }

            $transaction->status = 'approved';
        }

        $transaction->save();

        $this->sendEmailNotification($transaction);

        return response()->json(['ok' => true]);
    }

    /**
     * Queue invoice email on paid (only for email contacts).
     */
    protected function sendEmailNotification(Transaction $transaction): void
    {
        $contact = $transaction->customer_contact ?? '';

        if (! str_contains($contact, '@')) {
            Log::info('FanskuWebhook: WA contact, skipping email notification', [
                'transaction' => $transaction->code,
            ]);

            return;
        }

        try {
            Mail::to($contact)->queue(new \App\Mail\TransactionInvoice($transaction));
        } catch (\Exception $e) {
            Log::error('FanskuWebhook: failed to queue email', [
                'transaction' => $transaction->code,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
