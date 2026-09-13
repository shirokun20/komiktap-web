<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TripayCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'tripay.private_key' => 'test-private-key',
            // Callback tests assume an enabled gateway; the disabled case
            // is covered by test_callback_rejected_when_gateway_disabled.
            'tripay.is_enabled' => true,
        ]);
    }

    private function makeSignature(string $rawBody): string
    {
        return hash_hmac('sha256', $rawBody, 'test-private-key');
    }

    private function makeTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code'             => 'KURON-INV-20260101-TEST',
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'amount'           => 150000,
            'customer_contact' => 'test@example.com',
            'status'           => 'pending',
            'payment_method'   => 'BRIVA',
            'payment_details'  => '',
        ], $overrides));
    }

    // -------------------------------------------------------
    // 9.4 Invalid signature → 401
    // -------------------------------------------------------

    public function test_callback_with_invalid_signature_returns_401(): void
    {
        $payload = json_encode(['merchant_ref' => 'KURON-INV-20260101-TEST', 'status' => 'PAID']);

        $response = $this->postJson('/api/tripay/callback', json_decode($payload, true), [
            'X-Callback-Signature' => 'invalid-signature',
        ]);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------
    // 9.5 Unknown merchant_ref → 404
    // -------------------------------------------------------

    public function test_callback_with_unknown_merchant_ref_returns_404(): void
    {
        $payload   = json_encode(['merchant_ref' => 'UNKNOWN-REF', 'status' => 'PAID']);
        $signature = $this->makeSignature($payload);

        $response = $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------
    // 9.2 Callback PAID → approved + notification
    // -------------------------------------------------------

    public function test_callback_paid_approves_transaction_and_sends_email(): void
    {
        Mail::fake();

        $transaction = $this->makeTransaction();

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'PAID',
            'total_amount' => 150000,
            'total_fee'    => 2000,
            'paid_at'      => now()->timestamp,
        ]);
        $signature = $this->makeSignature($payload);

        $response = $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $transaction->refresh();
        $this->assertSame('approved', $transaction->status);
        $this->assertSame('PAID', $transaction->tripay_status);
        $this->assertNotNull($transaction->tripay_paid_at);
        $this->assertSame(2000.0, (float) $transaction->tripay_fee);

        // License should be generated for non-donation
        $this->assertNotNull($transaction->license_id);

        // Email should be queued
        Mail::assertQueued(\App\Mail\TransactionInvoice::class, function ($mail) use ($transaction) {
            return $mail->transaction->id === $transaction->id;
        });
    }

    // -------------------------------------------------------
    // 9.2 WA contact — no email sent
    // -------------------------------------------------------

    public function test_callback_paid_skips_email_for_wa_contact(): void
    {
        Mail::fake();

        $transaction = $this->makeTransaction(['customer_contact' => '08123456789']);

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'PAID',
            'total_amount' => 150000,
            'total_fee'    => 2000,
            'paid_at'      => now()->timestamp,
        ]);
        $signature = $this->makeSignature($payload);

        $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        Mail::assertNothingQueued();
    }

    // -------------------------------------------------------
    // 9.3 Callback FAILED → rejected
    // -------------------------------------------------------

    public function test_callback_failed_rejects_transaction(): void
    {
        $transaction = $this->makeTransaction();

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'FAILED',
        ]);
        $signature = $this->makeSignature($payload);

        $response = $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $response->assertStatus(200);

        $transaction->refresh();
        $this->assertSame('rejected', $transaction->status);
        $this->assertSame('FAILED', $transaction->tripay_status);
    }

    // -------------------------------------------------------
    // 9.3 Callback EXPIRED → rejected
    // -------------------------------------------------------

    public function test_callback_expired_rejects_transaction(): void
    {
        $transaction = $this->makeTransaction();

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'EXPIRED',
        ]);
        $signature = $this->makeSignature($payload);

        $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $transaction->refresh();
        $this->assertSame('rejected', $transaction->status);
    }

    // -------------------------------------------------------
    // Idempotency — already approved stays approved
    // -------------------------------------------------------

    public function test_callback_paid_is_idempotent_for_already_approved(): void
    {
        $transaction = $this->makeTransaction(['status' => 'approved']);

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'PAID',
            'total_amount' => 150000,
            'total_fee'    => 2000,
            'paid_at'      => now()->timestamp,
        ]);
        $signature = $this->makeSignature($payload);

        $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $transaction->refresh();
        // Status should remain approved, no duplicate license
        $this->assertSame('approved', $transaction->status);
    }

    // -------------------------------------------------------
    // Fail-closed: empty signing secret → always 401
    // -------------------------------------------------------

    public function test_callback_with_empty_secret_always_returns_401(): void
    {
        config(['tripay.private_key' => '']);

        $transaction = $this->makeTransaction();

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'PAID',
            'total_amount' => 150000,
            'total_fee'    => 2000,
            'paid_at'      => now()->timestamp,
        ]);
        // Signature yang valid untuk key kosong — dapat dihitung siapa pun.
        $forged = hash_hmac('sha256', $payload, '');

        $response = $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $forged,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $response->assertStatus(401);
        $this->assertSame('pending', $transaction->refresh()->status);
        $this->assertNull($transaction->license_id);
    }

    // -------------------------------------------------------
    // Callback flood → 429 (default 60,1)
    // -------------------------------------------------------

    public function test_callback_flood_returns_429_after_limit_exceeded(): void
    {
        $payload = ['merchant_ref' => 'UNKNOWN-REF', 'status' => 'PAID'];

        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/tripay/callback', $payload, [
                'X-Callback-Signature' => 'invalid',
            ])->assertStatus(401);
        }

        $this->postJson('/api/tripay/callback', $payload, [
            'X-Callback-Signature' => 'invalid',
        ])->assertStatus(429);
    }

    // -------------------------------------------------------
    // Gateway disabled → 404 without processing
    // -------------------------------------------------------

    public function test_callback_rejected_when_gateway_disabled(): void
    {
        config([
            'tripay.is_enabled' => false,
            'tripay.private_key' => 'test-private-key',
        ]);

        $transaction = $this->makeTransaction();

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'PAID',
            'total_amount' => 150000,
            'total_fee'    => 2000,
            'paid_at'      => now()->timestamp,
        ]);
        // Signature VALID — gating harus menolak sebelum validasi.
        $signature = hash_hmac('sha256', $payload, 'test-private-key');

        $response = $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $response->assertStatus(404);
        $this->assertSame('pending', $transaction->refresh()->status);
        $this->assertNull($transaction->license_id);
    }

    public function test_callback_disabled_with_invalid_signature_still_returns_404(): void
    {
        // Tripay tidak dipakai (gateway OFF): callback wajib 404 bahkan
        // sebelum validasi signature — signature invalid pun tetap 404,
        // bukan 401, dan tidak ada perubahan state.
        config([
            'tripay.is_enabled' => false,
            'tripay.private_key' => 'test-private-key',
        ]);

        $transaction = $this->makeTransaction(['code' => 'KURON-INV-20260101-DISABLED']);

        $payload = json_encode([
            'merchant_ref' => $transaction->code,
            'status'       => 'PAID',
            'total_amount' => 150000,
            'total_fee'    => 2000,
            'paid_at'      => now()->timestamp,
        ]);

        $response = $this->call('POST', '/api/tripay/callback', [], [], [], [
            'HTTP_X-Callback-Signature' => 'invalid-signature',
            'CONTENT_TYPE'              => 'application/json',
        ], $payload);

        $response->assertStatus(404);
        $this->assertSame('pending', $transaction->refresh()->status);
        $this->assertNull($transaction->license_id);
    }
}
