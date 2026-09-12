<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('checkout');
        RateLimiter::clear('contact');
        RateLimiter::clear('lookup');
        RateLimiter::clear('download');

        // Seed a payment method so checkout doesn't fail on method lookup
        $settings = app(PaymentSettings::class);
        $settings->is_enabled      = true;
        $settings->payment_methods = [
            [
                'name'            => 'BRIVA',
                'account_number'  => '1234567890',
                'account_holder'  => 'KomikTap',
                'usage_type'      => 'all',
                'instructions'    => '',
                'qris_image_path' => null,
                'qris_string'     => null,
            ],
        ];
        $settings->save();

        \App\Models\Plan::create(['name' => 'Starter', 'price' => 50000]);

        config(['tripay.is_enabled' => false]);

        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->fansku_enabled = false;
        $gateway->manual_enabled = true;
        $gateway->save();
    }

    // -------------------------------------------------------
    // 9.9 Rate limit on checkout (exceed → 429)
    // -------------------------------------------------------

    public function test_checkout_rate_limit_returns_429_after_limit_exceeded(): void
    {
        // NOTE: throttle: parameter is resolved at route registration, so
        // overriding tripay.rate_limit.checkout here has no effect.
        // Default is 5,10 → first 5 succeed, 6th is rate limited.
        $payload = [
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method'   => 'BRIVA',
        ];

        // First five requests should succeed (default limit 5 per 10 min)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/checkout', $payload)->assertStatus(200);
        }

        // Sixth should be rate limited
        $this->postJson('/api/checkout', $payload)->assertStatus(429);
    }

    // -------------------------------------------------------
    // 9.10 Rate limit on download (exceed → 429)
    // -------------------------------------------------------

    public function test_download_rate_limit_returns_429_after_limit_exceeded(): void
    {
        // NOTE: throttle: parameter is resolved at route registration, so
        // overriding tripay.rate_limit.download here has no effect.
        // Default is 10,10 → first 10 succeed, 11th is rate limited.
        // Create the dummy file so requests reach the throttle (not 500).
        \Illuminate\Support\Facades\Storage::disk('public')->put('apk/test.apk', 'dummy-apk');

        // Create a fake APK version
        $apk = \App\Models\ApkVersion::create([
            'version_name' => '1.0.0',
            'version_code' => '100',
            'file_path'    => 'apk/test.apk',
            'is_active'    => true,
            'download_count' => 0,
        ]);

        // Generate a valid signed URL
        $expires   = now()->addMinutes(30)->timestamp;
        $signature = hash_hmac('sha256', "download:{$apk->version_code}:{$expires}", config('app.key'));

        $url = "/download/{$apk->version_code}?expires={$expires}&signature={$signature}";

        // First ten requests succeed (default limit 10 per 10 min)
        for ($i = 0; $i < 10; $i++) {
            $this->get($url)->assertStatus(200);
        }

        // Eleventh should be rate limited
        $response = $this->get($url);
        $response->assertStatus(429);

        // Cleanup dummy file so other tests see the missing-file path
        \Illuminate\Support\Facades\Storage::disk('public')->delete('apk/test.apk');
    }
}
