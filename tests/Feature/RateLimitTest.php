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
    }

    // -------------------------------------------------------
    // 9.9 Rate limit on checkout (exceed → 429)
    // -------------------------------------------------------

    public function test_checkout_rate_limit_returns_429_after_limit_exceeded(): void
    {
        // Override to a very tight limit: 2 per minute for testing
        config(['tripay.rate_limit.checkout' => '2,1']);

        $payload = [
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'customer_contact' => 'buyer@example.com',
            'payment_method'   => 'BRIVA',
        ];

        // First two requests should succeed
        $this->postJson('/api/checkout', $payload)->assertStatus(200);
        $this->postJson('/api/checkout', $payload)->assertStatus(200);

        // Third should be rate limited
        $this->postJson('/api/checkout', $payload)->assertStatus(429);
    }

    // -------------------------------------------------------
    // 9.10 Rate limit on download (exceed → 429)
    // -------------------------------------------------------

    public function test_download_rate_limit_returns_429_after_limit_exceeded(): void
    {
        config(['tripay.rate_limit.download' => '2,1']);

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

        // First two requests — may fail with 404 (file not on disk) but not 429
        $this->get($url);
        $this->get($url);

        // Third should be rate limited
        $response = $this->get($url);
        $response->assertStatus(429);
    }
}
