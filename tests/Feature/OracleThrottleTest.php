<?php

namespace Tests\Feature;

use App\Models\MobileErrorReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OracleThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_voucher_flood_returns_429_after_limit_exceeded(): void
    {
        // Default 10,10: 11th request is throttled.
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/check-voucher', [
                'voucher_code' => 'NOT-A-CODE',
                'amount'       => 100000,
            ])->assertStatus(400);
        }

        $this->postJson('/api/check-voucher', [
            'voucher_code' => 'NOT-A-CODE',
            'amount'       => 100000,
        ])->assertStatus(429);
    }

    public function test_check_license_flood_returns_429_after_limit_exceeded(): void
    {
        // Default 10,10: 11th request is throttled.
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/check-license', [
                'license_key' => 'KURON-XXXX-XXXX-XXXX',
                'device_id'   => 'device-1',
            ])->assertStatus(404);
        }

        $this->postJson('/api/check-license', [
            'license_key' => 'KURON-XXXX-XXXX-XXXX',
            'device_id'   => 'device-1',
        ])->assertStatus(429);
    }

    public function test_error_report_flood_returns_429_after_limit_exceeded(): void
    {
        $payload = [
            'error_message' => 'NullPointerException',
            'device_info'   => 'Pixel 7, Android 14',
            'app_version'   => '1.0.5',
        ];

        // Default 10,10: 11th request is throttled.
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/error-report', $payload)->assertStatus(201);
        }

        $this->postJson('/api/error-report', $payload)->assertStatus(429);
    }

    public function test_error_report_rejects_oversized_input_without_storing(): void
    {
        $response = $this->postJson('/api/error-report', [
            'error_message' => str_repeat('x', 3000),
            'stack_trace'   => str_repeat('y', 100),
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, MobileErrorReport::count());
    }
}
