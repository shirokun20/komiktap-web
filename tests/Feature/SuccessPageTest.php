<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuccessPageTest extends TestCase
{
    use RefreshDatabase;

    private function loginAs(string $email = 'buyer@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $this->actingAs($user);

        return $user;
    }

    private function makeTransaction(string $code, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code' => $code,
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'amount' => 50000,
            'customer_contact' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'BCA',
            'payment_details' => 'No: 123',
        ], $overrides));
    }

    public function test_success_page_renders_all_statuses(): void
    {
        $this->loginAs();

        foreach (['pending', 'approved', 'rejected'] as $i => $status) {
            $tx = $this->makeTransaction("KURON-INV-20260101-TS{$i}", ['status' => $status]);

            $this->get('/success/' . $tx->code)
                ->assertOk()
                ->assertSee($tx->code, false);
        }
    }

    public function test_success_page_renders_fansku_and_voucher_rows(): void
    {
        $this->loginAs();

        $tx = $this->makeTransaction('KURON-INV-20260101-TS9', [
            'status' => 'pending',
            'payment_method' => 'QRIS (Fansku)',
            'fansku_support_id' => 'SUP-001',
            'fansku_raw_response' => ['fee' => 192, 'total_amount' => 32192],
            'discount_amount' => 5000,
            'voucher_code' => 'HEMAT5K',
        ]);

        $this->get('/success/' . $tx->code)
            ->assertOk()
            ->assertSee('Biaya Layanan QRIS', false)
            ->assertSee('HEMAT5K', false)
            ->assertSee('SUP-001', false);
    }
}
