<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\TripayService;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTripayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed PaymentSettings with a test method
        $settings = app(PaymentSettings::class);
        $settings->is_enabled     = true;
        $settings->payment_methods = [
            [
                'name'           => 'BRIVA',
                'account_number' => '1234567890',
                'account_holder' => 'KomikTap',
                'usage_type'     => 'all',
                'instructions'   => '',
                'qris_image_path'=> null,
                'qris_string'    => null,
            ],
        ];
        $settings->save();

        // Seed a Plan for standard plan tests
        \App\Models\Plan::create([
            'name'  => 'Starter',
            'price' => 50000,
        ]);

        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->fansku_enabled = false;
        $gateway->manual_enabled = true;
        $gateway->save();
    }

    // -------------------------------------------------------
    // 9.6 Checkout with Tripay enabled → stores reference
    // -------------------------------------------------------

    public function test_checkout_with_tripay_enabled_stores_reference(): void
    {
        config([
            'fansku.is_enabled'    => false,
            'tripay.is_enabled'    => true,
            'tripay.api_key'       => 'test-api-key',
            'tripay.private_key'   => 'test-private-key',
            'tripay.merchant_code' => 'T12345',
            'tripay.mode'          => 'sandbox',
            'tripay.base_url'      => [
                'sandbox'    => 'https://tripay.co.id/api-sandbox',
                'production' => 'https://tripay.co.id/api',
            ],
        ]);

        Http::fake([
            'tripay.co.id/api-sandbox/transaction/create' => Http::response([
                'success' => true,
                'data'    => [
                    'reference'    => 'DEV-REF-001',
                    'pay_code'     => '9876543210',
                    'checkout_url' => 'https://tripay.co.id/checkout/DEV-REF-001',
                    'expired_time' => now()->addHours(24)->timestamp,
                    'payment_method' => 'BRIVA',
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/checkout', [
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method'   => 'BRIVA',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.tripay.reference', 'DEV-REF-001')
                 ->assertJsonPath('data.tripay.pay_code', '9876543210');

        $transaction = Transaction::where('customer_contact', 'buyer@example.com')->first();
        $this->assertNotNull($transaction);
        $this->assertSame('DEV-REF-001', $transaction->tripay_reference);
    }

    // -------------------------------------------------------
    // 9.7 Checkout with Tripay disabled → manual fallback
    // -------------------------------------------------------

    public function test_checkout_with_tripay_disabled_falls_back_to_manual(): void
    {
        config(['tripay.is_enabled' => false]);

        $response = $this->postJson('/api/checkout', [
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method'   => 'BRIVA',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // No tripay key in response
        $this->assertArrayNotHasKey('tripay', $response->json('data'));

        // Transaction created without tripay reference
        $transaction = Transaction::where('customer_contact', 'buyer@example.com')->first();
        $this->assertNotNull($transaction);
        $this->assertNull($transaction->tripay_reference);
    }

    // -------------------------------------------------------
    // 9.7 Tripay API failure → graceful fallback (no exception)
    // -------------------------------------------------------

    public function test_checkout_falls_back_gracefully_when_tripay_api_fails(): void
    {
        config([
            'tripay.is_enabled'    => true,
            'tripay.api_key'       => 'test-api-key',
            'tripay.private_key'   => 'test-private-key',
            'tripay.merchant_code' => 'T12345',
            'tripay.mode'          => 'sandbox',
            'tripay.base_url'      => [
                'sandbox'    => 'https://tripay.co.id/api-sandbox',
                'production' => 'https://tripay.co.id/api',
            ],
        ]);

        Http::fake([
            'tripay.co.id/api-sandbox/transaction/create' => Http::response([
                'success' => false,
                'message' => 'Service unavailable',
            ], 503),
        ]);

        $response = $this->postJson('/api/checkout', [
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method'   => 'BRIVA',
        ]);

        // Should still succeed (manual fallback)
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $transaction = Transaction::where('customer_contact', 'buyer@example.com')->first();
        $this->assertNotNull($transaction);
        $this->assertNull($transaction->tripay_reference);
    }
}
