<?php

namespace Tests\Unit;

use App\Services\FanskuService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FanskuServiceTest extends TestCase
{
    protected FanskuService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'fansku.api_key' => 'test-api-key',
            'fansku.webhook_secret' => 'test-webhook-secret',
            'fansku.base_url' => 'https://api.fansku.id/api/v1/public-api',
            'fansku.payment_method' => 'qris',
            'fansku.methods_cache_ttl' => 600,
        ]);

        $this->service = new FanskuService();
    }

    // -------------------------------------------------------
    // createSupport — success
    // -------------------------------------------------------

    public function test_create_support_returns_support_id_and_qr_string(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/supports' => Http::response([
                'success' => true,
                'message' => 'Support berhasil dibuat',
                'data' => [
                    'id' => 'j74YK6BwPv',
                    'amount' => 50000,
                    'fee' => 300,
                    'total_amount' => 50300,
                    'payment' => [
                        'status' => 'REQUIRES_ACTION',
                        'channel_code' => 'QRIS',
                        'actions' => [
                            [
                                'type' => 'PRESENT_TO_CUSTOMER',
                                'value' => 'qr-string-abc',
                                'descriptor' => 'QR_STRING',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = $this->service->createSupport([
            'name' => 'John',
            'email' => 'john@example.com',
            'message' => 'KomikTap Starter',
            'amount' => 50000,
            'payment_method' => 'qris',
        ]);

        $this->assertSame('j74YK6BwPv', $result['support_id']);
        $this->assertSame('qr-string-abc', $result['qr_string']);
        $this->assertSame(50300, $result['total_amount']);
    }

    public function test_create_support_rejects_non_qris_before_network(): void
    {
        Http::fake();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->createSupport([
            'name' => 'John',
            'email' => 'john@example.com',
            'message' => 'test',
            'amount' => 50000,
            'payment_method' => 'bca',
        ]);

        Http::assertNothingSent();
    }

    public function test_create_support_throws_on_api_error(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/supports' => Http::response([
                'success' => false,
                'message' => 'Invalid amount',
            ], 422),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Fansku/');

        $this->service->createSupport([
            'name' => 'John',
            'email' => 'john@example.com',
            'message' => 'test',
            'amount' => 50000,
        ]);
    }

    // -------------------------------------------------------
    // getPaymentMethods + isQrisActive
    // -------------------------------------------------------

    public function test_is_qris_active_true_when_qris_enabled(): void
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
        ]);

        $this->assertTrue($this->service->isQrisActive());
    }

    public function test_is_qris_active_false_when_qris_missing(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/payment-methods' => Http::response([
                'success' => true,
                'message' => 'ok',
                'data' => [
                    [
                        'slug' => 'e-wallet',
                        'payment_methods' => [
                            ['slug' => 'gopay', 'name' => 'GoPay', 'is_active' => true],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->assertFalse($this->service->isQrisActive());
    }

    public function test_is_qris_active_false_on_api_error(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/payment-methods' => Http::response([
                'success' => false,
                'message' => 'boom',
            ], 500),
        ]);

        $this->assertFalse($this->service->isQrisActive());
    }

    // -------------------------------------------------------
    // validateWebhook
    // -------------------------------------------------------

    public function test_validate_webhook_true_for_valid_signature(): void
    {
        $rawBody = '{"event":"donation.paid","data":{"support_id":"abc"}}';
        $signature = hash_hmac('sha256', $rawBody, 'test-webhook-secret');

        $this->assertTrue($this->service->validateWebhook($rawBody, $signature));
    }

    public function test_validate_webhook_false_for_invalid_signature(): void
    {
        $this->assertFalse($this->service->validateWebhook('{}', 'bad-signature'));
    }

    public function test_validate_webhook_false_for_missing_signature(): void
    {
        $this->assertFalse($this->service->validateWebhook('{}', ''));
    }

    // -------------------------------------------------------
    // getBalance / getSupportsCount
    // -------------------------------------------------------

    public function test_get_balance_returns_available_and_pending(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/balance' => Http::response([
                'success' => true,
                'message' => 'Balance berhasil diambil',
                'data' => ['available_balance' => 6750000, 'pending_balance' => 250000],
            ]),
        ]);

        $result = $this->service->getBalance();

        $this->assertSame(6750000, $result['available_balance']);
        $this->assertSame(250000, $result['pending_balance']);
    }

    public function test_get_balance_throws_on_api_error(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/balance' => Http::response([
                'success' => false,
                'message' => 'boom',
            ], 500),
        ]);

        $this->expectException(\Exception::class);
        $this->service->getBalance();
    }

    public function test_get_supports_count_returns_totals(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/supports/count' => Http::response([
                'success' => true,
                'message' => 'ok',
                'data' => ['total_supports' => 150, 'total_amount' => 7500000, 'total_creator_earning' => 6750000],
            ]),
        ]);

        $result = $this->service->getSupportsCount();

        $this->assertSame(150, $result['total_supports']);
        $this->assertSame(6750000, $result['total_creator_earning']);
    }

    public function test_get_supports_returns_paginated_history(): void
    {
        Http::fake([
            'api.fansku.id/api/v1/public-api/supports*' => Http::response([
                'success' => true,
                'message' => 'ok',
                'data' => [
                    'data' => [['id' => 'aB3xK9', 'status' => 'paid']],
                    'meta' => ['current_page' => 1, 'total' => 1],
                ],
            ]),
        ]);

        $result = $this->service->getSupports(1, 10);

        $this->assertCount(1, $result['data']);
        $this->assertSame(1, $result['meta']['current_page']);
    }
}
