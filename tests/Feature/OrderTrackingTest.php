<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
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
    // GET /orders — index page loads (wajib login, otomatis email login)
    // -------------------------------------------------------

    public function test_orders_index_page_loads(): void
    {
        $this->get('/orders')->assertRedirect(route('login'));

        $this->loginAs();
        $this->get('/orders')->assertStatus(200);
    }

    // -------------------------------------------------------
    // POST /orders/lookup — orders found (dikunci ke email login)
    // -------------------------------------------------------

    public function test_lookup_returns_transactions_for_matching_contact(): void
    {
        $this->loginAs();
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
        $this->loginAs('nobody@example.com');

        $response = $this->post('/orders/lookup', ['contact' => 'nobody@example.com']);

        $response->assertStatus(200);
        $response->assertSee('Belum ada pesanan');
    }

    // -------------------------------------------------------
    // POST /orders/lookup — input manual diabaikan, ikut email login
    // -------------------------------------------------------

    public function test_lookup_ignores_manual_contact_and_uses_login_email(): void
    {
        $this->loginAs();
        $this->makeTransaction();

        $response = $this->post('/orders/lookup', ['contact' => 'other@example.com']);

        $response->assertStatus(200);
        $response->assertSee('KURON-INV-20260101-TEST');
    }

    // -------------------------------------------------------
    // GET /orders/{code} — show detail (hanya milik sendiri)
    // -------------------------------------------------------

    public function test_show_returns_transaction_detail(): void
    {
        $this->loginAs();
        $transaction = $this->makeTransaction();

        $response = $this->get('/orders/' . $transaction->code);

        $response->assertStatus(200);
        $response->assertSee($transaction->code);
        $response->assertSee('Starter');
    }

    public function test_show_rejects_other_email_transaction(): void
    {
        $this->loginAs();
        $transaction = $this->makeTransaction(['customer_contact' => 'other@example.com']);

        $this->get('/orders/' . $transaction->code)->assertStatus(404);
    }

    // -------------------------------------------------------
    // GET /orders/{code} — 404 for unknown code
    // -------------------------------------------------------

    public function test_show_returns_404_for_unknown_code(): void
    {
        $this->loginAs();

        $this->get('/orders/UNKNOWN-CODE')->assertStatus(404);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/orders')->assertRedirect(route('login'));
        $this->post('/orders/lookup', ['contact' => 'buyer@example.com'])->assertRedirect(route('login'));
        $this->get('/orders/SOME-CODE')->assertRedirect(route('login'));
    }
}
