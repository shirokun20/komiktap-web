<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FanskuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'fansku.api_key' => 'test-api-key',
            'fansku.webhook_secret' => 'test-webhook-secret',
            'fansku.base_url' => 'https://api.fansku.id/api/v1/public-api',
            'fansku.payment_method' => 'qris',
            'fansku.methods_cache_ttl' => 600,
            'tripay.is_enabled' => false,
        ]);

        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->fansku_enabled = true;
        $gateway->manual_enabled = false;
        $gateway->save();

        $settings = app(PaymentSettings::class);
        $settings->is_enabled = true;
        $settings->payment_methods = [
            [
                'name' => 'QRIS Manual',
                'account_number' => null,
                'account_holder' => 'KomikTap',
                'usage_type' => 'all',
                'instructions' => '',
                'qris_image_path' => null,
                'qris_string' => null,
            ],
        ];
        $settings->save();

        \App\Models\Plan::create(['name' => 'Starter', 'price' => 50000]);

        Mail::fake();
    }

    private function fakeQrisActive(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/payment-methods' => Http::response([
                'success' => true,
                'message' => 'ok',
                'data' => [
                    [
                        'slug' => 'pembayaran-qris',
                        'payment_methods' => [
                            ['slug' => 'qris', 'name' => 'QRIS', 'is_active' => true],
                        ],
                    ],
                ],
            ]),
            'api.fansku.id/api/v1/public-api/supports' => Http::response([
                'success' => true,
                'message' => 'Support berhasil dibuat',
                'data' => [
                    'id' => 'SUP-001',
                    'amount' => 50000,
                    'fee' => 300,
                    'total_amount' => 50300,
                    'payment' => [
                        'actions' => [
                            ['type' => 'PRESENT_TO_CUSTOMER', 'value' => 'qr-abc', 'descriptor' => 'QR_STRING'],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    private function makeTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code' => 'KURON-INV-20260101-FAN1',
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'amount' => 50000,
            'customer_contact' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'QRIS (Fansku)',
            'payment_details' => '',
            'fansku_support_id' => 'SUP-001',
            'fansku_status' => 'pending',
        ], $overrides));
    }

    private function webhookPayload(array $overrides = []): string
    {
        return json_encode(array_merge([
            'event' => 'donation.paid',
            'data' => [
                'support_id' => 'SUP-001',
                'code' => 'INV-20260101-0001',
                'supporter_name' => 'Buyer',
                'amount' => 50000,
                'currency' => 'IDR',
                'message' => 'KomikTap',
                'created_at' => '2026-01-01T10:00:00.000Z',
            ],
            'is_test' => false,
            'sent_at' => '2026-01-01T10:00:05.000Z',
        ], $overrides));
    }

    private function postWebhook(string $rawBody): \Illuminate\Testing\TestResponse
    {
        $signature = hash_hmac('sha256', $rawBody, 'test-webhook-secret');

        return $this->call('POST', '/api/fansku/webhook', [], [], [], [
            'HTTP_X-Fansku-Signature' => $signature,
            'HTTP_X-Fansku-Event' => 'donation.paid',
            'CONTENT_TYPE' => 'application/json',
        ], $rawBody);
    }

    // -------------------------------------------------------
    // Webhook: invalid signature → 403
    // -------------------------------------------------------

    public function test_webhook_invalid_signature_returns_403(): void
    {
        $this->makeTransaction();

        $response = $this->call('POST', '/api/fansku/webhook', [], [], [], [
            'HTTP_X-Fansku-Signature' => 'bad',
            'CONTENT_TYPE' => 'application/json',
        ], $this->webhookPayload());

        $response->assertStatus(403);
        $this->assertSame('pending', Transaction::first()->status);
    }

    // -------------------------------------------------------
    // Webhook: non-paid event → 200 no mutation
    // -------------------------------------------------------

    public function test_webhook_non_paid_event_acks_without_mutation(): void
    {
        $this->makeTransaction();

        $rawBody = json_encode(['event' => 'donation.created', 'data' => ['support_id' => 'SUP-001'], 'is_test' => false]);
        $response = $this->postWebhook($rawBody);

        $response->assertOk();
        $this->assertSame('pending', Transaction::first()->status);
    }

    // -------------------------------------------------------
    // Webhook: test mode → 200 no mutation
    // -------------------------------------------------------

    public function test_webhook_test_mode_does_not_mutate(): void
    {
        $this->makeTransaction();

        $payload = json_decode($this->webhookPayload(), true);
        $payload['is_test'] = true;

        $response = $this->postWebhook(json_encode($payload));

        $response->assertOk();
        $this->assertSame('pending', Transaction::first()->status);
    }

    // -------------------------------------------------------
    // Webhook: unknown identifier → 404
    // -------------------------------------------------------

    public function test_webhook_unknown_support_returns_404(): void
    {
        $payload = json_decode($this->webhookPayload(), true);
        $payload['data']['support_id'] = 'UNKNOWN';
        unset($payload['data']['code']);

        $response = $this->postWebhook(json_encode($payload));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------
    // Webhook: paid order → approved + license + email
    // -------------------------------------------------------

    public function test_webhook_paid_approves_order_and_creates_license(): void
    {
        $this->makeTransaction();

        $response = $this->postWebhook($this->webhookPayload());

        $response->assertOk();

        $tx = Transaction::first();
        $this->assertSame('approved', $tx->status);
        $this->assertSame('paid', $tx->fansku_status);
        $this->assertSame('INV-20260101-0001', $tx->fansku_code);
        $this->assertNotNull($tx->fansku_paid_at);
        $this->assertNotNull($tx->license_id);

        Mail::assertQueued(\App\Mail\TransactionInvoice::class);
    }

    // -------------------------------------------------------
    // Webhook: duplicate → idempotent, single license
    // -------------------------------------------------------

    public function test_webhook_duplicate_does_not_create_second_license(): void
    {
        $this->makeTransaction();
        $rawBody = $this->webhookPayload();

        $this->postWebhook($rawBody)->assertOk();
        $this->postWebhook($rawBody)->assertOk();

        $this->assertSame(1, \App\Models\License::count());
        $this->assertSame('approved', Transaction::first()->status);
    }

    // -------------------------------------------------------
    // Webhook: paid donation → approved, no license
    // -------------------------------------------------------

    public function test_webhook_paid_donation_has_no_license(): void
    {
        $this->makeTransaction([
            'code' => 'KURON-PEDULI-20260101-ABCD',
            'plan_name' => 'Donasi',
        ]);

        $this->postWebhook($this->webhookPayload())->assertOk();

        $tx = Transaction::first();
        $this->assertSame('approved', $tx->status);
        $this->assertNull($tx->license_id);
    }

    // -------------------------------------------------------
    // Checkout: Fansku enabled → QR returned
    // -------------------------------------------------------

    public function test_checkout_with_fansku_returns_qr(): void
    {
        $this->fakeQrisActive();

        $response = $this->postJson('/api/checkout', [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method' => 'QRIS Otomatis',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.fansku.support_id', 'SUP-001')
            ->assertJsonPath('data.fansku.qr_string', 'qr-abc');

        $this->assertSame('SUP-001', Transaction::first()->fansku_support_id);
    }

    // -------------------------------------------------------
    // Checkout: WA contact → manual fallback
    // -------------------------------------------------------

    public function test_checkout_wa_contact_falls_back_to_manual(): void
    {
        $this->fakeQrisActive();

        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->manual_enabled = true;
        $gateway->save();

        $response = $this->postJson('/api/checkout', [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => '08123456789',
            'payment_method' => 'QRIS Manual',
        ]);

        $response->assertOk();
        $this->assertArrayNotHasKey('fansku', $response->json('data'));
        $this->assertNull(Transaction::first()->fansku_support_id);
    }

    // -------------------------------------------------------
    // Checkout: flag off → manual fallback
    // -------------------------------------------------------

    public function test_checkout_fansku_disabled_falls_back_to_manual(): void
    {
        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->fansku_enabled = false;
        $gateway->manual_enabled = true;
        $gateway->save();
        $this->fakeQrisActive();

        $response = $this->postJson('/api/checkout', [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method' => 'QRIS Manual',
        ]);

        $response->assertOk();
        $this->assertArrayNotHasKey('fansku', $response->json('data'));
    }

    // -------------------------------------------------------
    // Checkout: manual disabled → rejected
    // -------------------------------------------------------

    public function test_checkout_manual_disabled_rejects_manual_method(): void
    {
        $this->fakeQrisActive();

        $response = $this->postJson('/api/checkout', [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method' => 'QRIS Manual',
        ]);

        $response->assertStatus(400);
    }

    // -------------------------------------------------------
    // Checkout: donation via Fansku
    // -------------------------------------------------------

    public function test_checkout_donation_via_fansku(): void
    {
        $this->fakeQrisActive();

        $response = $this->postJson('/api/checkout', [
            'plan_name' => 'Donasi',
            'device_quota' => 1,
            'duration_months' => 1,
            'amount' => 25000,
            'customer_contact' => 'donor@example.com',
            'payment_method' => 'QRIS Otomatis',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.fansku.qr_string', 'qr-abc');

        $tx = Transaction::first();
        $this->assertStringStartsWith('KURON-PEDULI-', $tx->code);
        $this->assertSame('SUP-001', $tx->fansku_support_id);
    }

    // -------------------------------------------------------
    // API: payment-methods hides manual when fansku-only
    // -------------------------------------------------------

    public function test_payment_methods_hidden_when_fansku_only(): void
    {
        $response = $this->getJson('/api/payment-methods?type=order');

        $response->assertOk()
            ->assertJsonPath('data.fansku_enabled', true)
            ->assertJsonCount(0, 'data.payment_methods');
    }

    public function test_payment_methods_shown_when_manual_enabled(): void
    {
        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->manual_enabled = true;
        $gateway->save();

        $response = $this->getJson('/api/payment-methods?type=order');

        $response->assertOk()
            ->assertJsonCount(1, 'data.payment_methods');
    }
}
