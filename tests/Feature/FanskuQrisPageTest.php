<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FanskuQrisPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeFanskuTransaction(string $code, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code' => $code,
            'fansku_support_id' => 'SUP-' . substr($code, -4),
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'amount' => 32000,
            'customer_contact' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'QRIS (Fansku)',
            'payment_details' => 'QRIS otomatis via Fansku',
            'fansku_status' => 'pending',
            'fansku_raw_response' => [
                'fee' => 192,
                'total_amount' => 32192,
                'payment' => [
                    'actions' => [
                        ['type' => 'PRESENT_TO_CUSTOMER', 'descriptor' => 'QR_STRING', 'value' => 'QR-TEST-123'],
                    ],
                ],
            ],
        ], $overrides));
    }

    public function test_qris_page_shows_qr_for_pending(): void
    {
        $tx = $this->makeFanskuTransaction('KURON-INV-20260101-QR01');

        $this->get('/bayar/qris/' . $tx->code)
            ->assertOk()
            ->assertSee('QR-TEST-123', false)
            ->assertSee('32.192', false)
            ->assertSee('Starter', false)
            ->assertSee($tx->code, false);
    }

    public function test_qris_page_redirects_manual_to_success(): void
    {
        $tx = $this->makeFanskuTransaction('KURON-INV-20260101-QR02', [
            'payment_method' => 'BCA',
            'fansku_support_id' => null,
            'fansku_status' => null,
            'fansku_raw_response' => null,
        ]);

        $this->get('/bayar/qris/' . $tx->code)
            ->assertRedirect('/success/' . $tx->code);
    }

    public function test_qris_page_redirects_finished_to_success(): void
    {
        $approved = $this->makeFanskuTransaction('KURON-INV-20260101-QR03', ['status' => 'approved']);
        $rejected = $this->makeFanskuTransaction('KURON-INV-20260101-QR04', ['status' => 'rejected']);

        $this->get('/bayar/qris/' . $approved->code)->assertRedirect('/success/' . $approved->code);
        $this->get('/bayar/qris/' . $rejected->code)->assertRedirect('/success/' . $rejected->code);
    }

    public function test_qris_page_404_for_unknown_code(): void
    {
        $this->get('/bayar/qris/KURON-INV-20260101-NOPE')->assertNotFound();
    }

    public function test_status_api_returns_qr_data(): void
    {
        $tx = $this->makeFanskuTransaction('KURON-INV-20260101-QR05');

        $this->getJson('/api/checkout/fansku/' . $tx->code)
            ->assertOk()
            ->assertJsonPath('data.transaction_code', $tx->code)
            ->assertJsonPath('data.qr_string', 'QR-TEST-123')
            ->assertJsonPath('data.amount', 32000)
            ->assertJsonPath('data.fee', 192)
            ->assertJsonPath('data.total_amount', 32192)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_status_api_404_for_manual(): void
    {
        $tx = $this->makeFanskuTransaction('KURON-INV-20260101-QR06', [
            'payment_method' => 'BCA',
            'fansku_support_id' => null,
            'fansku_raw_response' => null,
        ]);

        $this->getJson('/api/checkout/fansku/' . $tx->code)->assertNotFound();
    }
}
