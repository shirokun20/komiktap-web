<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryQrisTest extends TestCase
{
    use RefreshDatabase;

    private function loginAs(string $email = 'buyer@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $this->actingAs($user);

        return $user;
    }

    private function makeTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code' => 'KURON-INV-20260101-HQ01',
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'amount' => 150000,
            'customer_contact' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'QRIS (Fansku)',
            'payment_details' => 'QRIS otomatis via Fansku',
        ], $overrides));
    }

    private function fanskuRaw(): array
    {
        return [
            'fee' => 900,
            'total_amount' => 150900,
            'payment' => [
                'actions' => [
                    ['type' => 'PRESENT_TO_CUSTOMER', 'descriptor' => 'QR_STRING', 'value' => 'QR-HISTORY-123'],
                ],
            ],
        ];
    }

    public function test_pending_fansku_detail_shows_qris_panel(): void
    {
        $this->loginAs();
        $tx = $this->makeTransaction([
            'fansku_support_id' => 'SUP-123',
            'fansku_status' => 'pending',
            'fansku_raw_response' => $this->fanskuRaw(),
        ]);

        $this->get('/riwayat/' . $tx->code)
            ->assertOk()
            ->assertSee('Lanjutkan Pembayaran QRIS', false)
            ->assertSee('qrBox', false)
            ->assertSee('Cek Ulang Status', false)
            ->assertSee('/bayar/qris/' . $tx->code, false)
            ->assertSee('150.900', false)
            ->assertSee('QR-HISTORY-123', false);
    }

    public function test_pending_manual_detail_hides_qris_panel(): void
    {
        $this->loginAs();
        $tx = $this->makeTransaction(['payment_method' => 'BRIVA']);

        $this->get('/riwayat/' . $tx->code)
            ->assertOk()
            ->assertSee('Menunggu Konfirmasi Pembayaran', false)
            ->assertDontSee('Lanjutkan Pembayaran QRIS', false)
            ->assertDontSee('qrBox', false)
            ->assertDontSee('cekUlangStatus', false);
    }

    public function test_approved_fansku_detail_hides_qris_panel(): void
    {
        $this->loginAs();
        $tx = $this->makeTransaction([
            'status' => 'approved',
            'fansku_support_id' => 'SUP-123',
            'fansku_raw_response' => $this->fanskuRaw(),
        ]);

        $this->get('/riwayat/' . $tx->code)
            ->assertOk()
            ->assertDontSee('Lanjutkan Pembayaran QRIS', false)
            ->assertDontSee('qrBox', false);
    }

    public function test_pending_donation_with_fansku_shows_qris_panel(): void
    {
        $this->loginAs();
        $tx = $this->makeTransaction([
            'code' => 'KURON-PEDULI-20260101-HQ01',
            'plan_name' => 'Donasi',
            'fansku_support_id' => 'SUP-456',
            'fansku_status' => 'pending',
            'fansku_raw_response' => $this->fanskuRaw(),
        ]);

        $this->get('/riwayat/' . $tx->code)
            ->assertOk()
            ->assertSee('Lanjutkan Pembayaran QRIS', false);
    }
}
