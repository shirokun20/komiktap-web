<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code'             => 'KURON-INV-20260101-TEST',
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'amount'           => 150000,
            'customer_contact' => 'buyer@example.com',
            'status'           => 'pending',
            'payment_method'   => 'BRIVA',
            'payment_details'  => '',
        ], $overrides));
    }

    // -------------------------------------------------------
    // GET /orders — index page loads
    // -------------------------------------------------------

    public function test_orders_index_page_loads(): void
    {
        $this->get('/orders')->assertStatus(200);
    }

    // -------------------------------------------------------
    // POST /orders/lookup — orders found
    // -------------------------------------------------------

    public function test_lookup_returns_transactions_for_matching_contact(): void
    {
        $this->makeTransaction();

        $response = $this->post('/orders/lookup', ['contact' => 'buyer@example.com']);

        $response->assertStatus(200);
        $response->assertSee('KURON-INV-20260101-TEST');
    }

    // -------------------------------------------------------
    // POST /orders/lookup — no orders found
    // -------------------------------------------------------

    public function test_lookup_shows_empty_state_when_no_orders(): void
    {
        $response = $this->post('/orders/lookup', ['contact' => 'nobody@example.com']);

        $response->assertStatus(200);
        $response->assertSee('Tidak ada pesanan ditemukan');
    }

    // -------------------------------------------------------
    // POST /orders/lookup — empty input validation
    // -------------------------------------------------------

    public function test_lookup_validates_empty_contact(): void
    {
        $response = $this->post('/orders/lookup', ['contact' => '']);

        $response->assertSessionHasErrors('contact');
    }

    // -------------------------------------------------------
    // GET /orders/{code} — show detail
    // -------------------------------------------------------

    public function test_show_returns_transaction_detail(): void
    {
        $transaction = $this->makeTransaction();

        $response = $this->get('/orders/' . $transaction->code);

        $response->assertStatus(200);
        $response->assertSee($transaction->code);
        $response->assertSee('Starter');
    }

    // -------------------------------------------------------
    // GET /orders/{code} — 404 for unknown code
    // -------------------------------------------------------

    public function test_show_returns_404_for_unknown_code(): void
    {
        $this->get('/orders/UNKNOWN-CODE')->assertStatus(404);
    }
}
