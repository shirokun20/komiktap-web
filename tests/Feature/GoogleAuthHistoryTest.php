<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthHistoryTest extends TestCase
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

    private function fakeGoogleUser(string $email = 'buyer@example.com', string $id = 'google-123', bool $verified = true): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->map([
            'id' => $id,
            'name' => 'Buyer Test',
            'email' => $email,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
        $user->setRaw([
            'sub' => $id,
            'name' => 'Buyer Test',
            'email' => $email,
            'picture' => 'https://example.com/avatar.jpg',
            'email_verified' => $verified,
        ]);

        return $user;
    }

    // -------------------------------------------------------
    // 2.1 Redirect + callback: login pertama membuat satu User
    // -------------------------------------------------------

    public function test_google_redirect_points_to_google(): void
    {
        $response = $this->get('/auth/google');

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_google_callback_creates_user_on_first_login(): void
    {
        Socialite::fake('google', $this->fakeGoogleUser());

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('history.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.com',
            'google_id' => 'google-123',
        ]);
    }

    public function test_google_callback_relogin_does_not_duplicate(): void
    {
        Socialite::fake('google', $this->fakeGoogleUser());

        $this->get('/auth/google/callback');
        $this->post('/logout');
        $this->assertGuest();

        $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
    }

    // -------------------------------------------------------
    // 2.2 Callback gagal: batal / email tak terverifikasi
    // -------------------------------------------------------

    public function test_google_callback_cancelled_returns_with_error(): void
    {
        $response = $this->get('/auth/google/callback?error=access_denied');

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_callback_rejects_unverified_email(): void
    {
        Socialite::fake('google', $this->fakeGoogleUser(verified: false));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    // -------------------------------------------------------
    // 2.3 Logout
    // -------------------------------------------------------

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // -------------------------------------------------------
    // 2.4 Tombol login di halaman publik
    // -------------------------------------------------------

    public function test_homepage_shows_google_login_button(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Masuk dengan Google', false);
        $response->assertSee(route('auth.google.redirect'), false);
    }

    // -------------------------------------------------------
    // 2.5 Non-admin ditolak membuka panel Filament
    // -------------------------------------------------------

    public function test_google_customer_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $this->actingAs($user);

        $this->assertFalse($user->canAccessPanel(new \Filament\Panel('admin')));
        $this->get('/admin')->assertStatus(403);
    }

    // -------------------------------------------------------
    // 3.1 Riwayat milik email pengguna
    // -------------------------------------------------------

    public function test_history_lists_own_transactions_newest_first(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $this->actingAs($user);

        $this->makeTransaction(['code' => 'KURON-INV-20260101-OLD', 'created_at' => now()->subDay()]);
        $this->makeTransaction(['code' => 'KURON-INV-20260101-NEW', 'created_at' => now()]);

        $response = $this->get('/riwayat');

        $response->assertStatus(200);
        $response->assertSee('KURON-INV-20260101-NEW');
        $response->assertSee('KURON-INV-20260101-OLD');
        $response->assertSee('Starter');
        $response->assertSee('150.000');
        $response->assertSee('pending');
    }

    // -------------------------------------------------------
    // 3.2 Tamu dialihkan ke login
    // -------------------------------------------------------

    public function test_guest_is_redirected_from_history(): void
    {
        $response = $this->get('/riwayat');

        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------
    // 3.3 Status kosong + WA lama tidak tampil
    // -------------------------------------------------------

    public function test_history_empty_state_for_new_email(): void
    {
        $user = User::factory()->create(['email' => 'newbie@example.com']);
        $this->actingAs($user);

        $this->makeTransaction(['customer_contact' => '08123456789']);

        $response = $this->get('/riwayat');

        $response->assertStatus(200);
        $response->assertSee('Belum ada riwayat pembelian');
        $response->assertDontSee('KURON-INV-20260101-TEST');
    }

    // -------------------------------------------------------
    // 3.4 Detail milik sendiri vs milik orang lain
    // -------------------------------------------------------

    public function test_history_detail_own_transaction(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $this->actingAs($user);

        $transaction = $this->makeTransaction();

        $response = $this->get('/riwayat/' . $transaction->code);

        $response->assertStatus(200);
        $response->assertSee($transaction->code);
    }

    public function test_history_detail_other_email_is_forbidden(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $this->actingAs($user);

        $transaction = $this->makeTransaction(['customer_contact' => 'other@example.com']);

        $this->get('/riwayat/' . $transaction->code)->assertStatus(404);
    }
}
