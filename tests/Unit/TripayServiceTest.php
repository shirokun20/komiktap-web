<?php

namespace Tests\Unit;

use App\Services\TripayService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TripayServiceTest extends TestCase
{
    protected TripayService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'tripay.api_key'       => 'test-api-key',
            'tripay.private_key'   => 'test-private-key',
            'tripay.merchant_code' => 'T12345',
            'tripay.mode'          => 'sandbox',
            'tripay.base_url'      => [
                'sandbox'    => 'https://tripay.co.id/api-sandbox',
                'production' => 'https://tripay.co.id/api',
            ],
        ]);

        $this->service = new TripayService();
    }

    // -------------------------------------------------------
    // 2.7 Signature builder
    // -------------------------------------------------------

    public function test_build_signature_returns_correct_hmac(): void
    {
        $merchantRef = 'KURON-INV-20260101-ABCD';
        $amount      = 150000;

        $expected = hash_hmac('sha256', 'T12345' . $merchantRef . $amount, 'test-private-key');
        $actual   = $this->service->buildSignature($merchantRef, $amount);

        $this->assertSame($expected, $actual);
    }

    // -------------------------------------------------------
    // 2.6 Callback validation
    // -------------------------------------------------------

    public function test_validate_callback_returns_true_for_valid_signature(): void
    {
        $rawBody   = '{"merchant_ref":"KURON-INV-20260101-ABCD","status":"PAID"}';
        $signature = hash_hmac('sha256', $rawBody, 'test-private-key');

        $this->assertTrue($this->service->validateCallback($rawBody, $signature));
    }

    public function test_validate_callback_returns_false_for_invalid_signature(): void
    {
        $rawBody = '{"merchant_ref":"KURON-INV-20260101-ABCD","status":"PAID"}';

        $this->assertFalse($this->service->validateCallback($rawBody, 'wrong-signature'));
    }

    // -------------------------------------------------------
    // 2.2 Create transaction
    // -------------------------------------------------------

    public function test_create_transaction_returns_data_on_success(): void
    {
        Http::fake([
            'tripay.co.id/api-sandbox/transaction/create' => Http::response([
                'success' => true,
                'data'    => [
                    'reference'    => 'DEV-REF-001',
                    'pay_code'     => '1234567890',
                    'checkout_url' => 'https://tripay.co.id/checkout/DEV-REF-001',
                    'expired_time' => now()->addHours(24)->timestamp,
                ],
            ], 200),
        ]);

        $result = $this->service->createTransaction([
            'method'         => 'BRIVA',
            'merchant_ref'   => 'KURON-INV-20260101-ABCD',
            'amount'         => 150000,
            'customer_name'  => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '08123456789',
            'order_items'    => [['name' => 'Starter', 'price' => 150000, 'quantity' => 1]],
        ]);

        $this->assertSame('DEV-REF-001', $result['reference']);
        $this->assertSame('1234567890', $result['pay_code']);
    }

    public function test_create_transaction_throws_on_api_error(): void
    {
        Http::fake([
            'tripay.co.id/api-sandbox/transaction/create' => Http::response([
                'success' => false,
                'message' => 'Invalid merchant code',
            ], 400),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Invalid merchant code/');

        $this->service->createTransaction([
            'method'         => 'BRIVA',
            'merchant_ref'   => 'KURON-INV-20260101-ABCD',
            'amount'         => 150000,
            'customer_name'  => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '08123456789',
            'order_items'    => [],
        ]);
    }

    // -------------------------------------------------------
    // 2.4 Payment channels
    // -------------------------------------------------------

    public function test_get_payment_channels_returns_array(): void
    {
        Http::fake([
            'tripay.co.id/api-sandbox/merchant/payment-channel' => Http::response([
                'success' => true,
                'data'    => [
                    ['code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'group' => 'Virtual Account', 'type' => 'direct', 'active' => true],
                    ['code' => 'QRIS',  'name' => 'QRIS',                'group' => 'QRIS',            'type' => 'direct', 'active' => true],
                ],
            ], 200),
        ]);

        $channels = $this->service->getPaymentChannels();

        $this->assertCount(2, $channels);
        $this->assertSame('BRIVA', $channels[0]['code']);
    }

    // -------------------------------------------------------
    // 2.3 Payment instruction
    // -------------------------------------------------------

    public function test_get_payment_instruction_returns_steps(): void
    {
        Http::fake([
            'tripay.co.id/api-sandbox/payment/instruction*' => Http::response([
                'success' => true,
                'data'    => [
                    ['title' => 'ATM BRI', 'steps' => ['Masukkan kartu', 'Pilih Transfer']],
                ],
            ], 200),
        ]);

        $instructions = $this->service->getPaymentInstruction('BRIVA');

        $this->assertNotEmpty($instructions);
        $this->assertSame('ATM BRI', $instructions[0]['title']);
    }
}
