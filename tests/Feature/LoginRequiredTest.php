<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use App\Models\Transaction;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginRequiredTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $settings = app(\App\Settings\PaymentSettings::class);
        $settings->is_enabled = true;
        $settings->payment_methods = [
            [
                'name' => 'BRIVA',
                'account_number' => '1234567890',
                'account_holder' => 'KomikTap',
                'usage_type' => 'all',
                'instructions' => '',
                'qris_image_path' => null,
                'qris_string' => null,
            ],
        ];
        $settings->save();

        \App\Models\Plan::create(['name' => 'Starter', 'price' => 50000]);

        config(['tripay.is_enabled' => false]);

        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->fansku_enabled = false;
        $gateway->manual_enabled = true;
        $gateway->save();
    }

    private function loginAs(string $email = 'buyer@example.com'): User
    {
        $user = User::factory()->create(['email' => $email]);
        $this->actingAs($user);

        return $user;
    }

    private function makeTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code' => 'KURON-INV-20260101-TEST',
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'amount' => 50000,
            'customer_contact' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'BRIVA',
            'payment_details' => '',
        ], $overrides));
    }

    private function makeCampaign(): DonationCampaign
    {
        return DonationCampaign::create([
            'title' => 'Bantu Sekolah',
            'slug' => 'bantu-sekolah',
            'description' => 'Donasi pendidikan',
            'target_amount' => 1000000,
            'is_active' => true,
        ]);
    }

    // -------------------------------------------------------
    // Tamu diarahkan ke login dari semua halaman protected
    // -------------------------------------------------------

    public function test_guest_is_redirected_to_login_from_all_protected_pages(): void
    {
        $campaign = $this->makeCampaign();
        $tx = $this->makeTransaction();

        $this->get('/donasi')->assertRedirect(route('login'));
        $this->get('/donasi/' . $campaign->slug)->assertRedirect(route('login'));
        $this->get('/donasi/' . $campaign->slug . '/bayar')->assertRedirect(route('login'));
        $this->get('/orders')->assertRedirect(route('login'));
        $this->post('/orders/lookup', ['contact' => 'x'])->assertRedirect(route('login'));
        $this->get('/orders/' . $tx->code)->assertRedirect(route('login'));
        $this->get('/bayar')->assertRedirect(route('login'));
        $this->get('/bayar/qris/' . $tx->code)->assertRedirect(route('login'));
        $this->get('/success/' . $tx->code)->assertRedirect(route('login'));
        $this->get('/invoices/' . $tx->code)->assertRedirect(route('login'));
        $this->get('/riwayat')->assertRedirect(route('login'));
        $this->get('/riwayat/' . $tx->code)->assertRedirect(route('login'));
    }

    public function test_guest_cannot_post_checkout(): void
    {
        $payload = [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => 'guest@example.com',
            'payment_method' => 'BRIVA',
        ];

        // Web checkout → redirect ke login (memulai Google OAuth).
        $this->post('/checkout', $payload)->assertRedirect(route('login'));

        // API checkout → 401 JSON.
        $this->postJson('/api/checkout', $payload)->assertStatus(401);
        $this->postJson('/api/checkout/fansku', $payload)->assertStatus(401);

        $tx = $this->makeTransaction();
        $this->getJson('/api/checkout/fansku/' . $tx->code)->assertStatus(401);

        $this->assertSame(1, Transaction::count());
    }

    // -------------------------------------------------------
    // User login bisa checkout (web session); kontak dikunci
    // -------------------------------------------------------

    public function test_logged_in_web_checkout_locks_contact_to_login_email(): void
    {
        $this->loginAs();

        $response = $this->post('/checkout', [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => 'other@example.com',
            'payment_method' => 'BRIVA',
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('transactions', ['customer_contact' => 'buyer@example.com']);
        $this->assertDatabaseMissing('transactions', ['customer_contact' => 'other@example.com']);
    }

    public function test_fansku_checkout_locks_contact_to_login_email(): void
    {
        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $gateway->fansku_enabled = true;
        $gateway->save();

        config([
            'fansku.api_key' => 'test-api-key',
            'fansku.base_url' => 'https://api.fansku.id/api/v1/public-api',
            'fansku.payment_method' => 'qris',
            'fansku.methods_cache_ttl' => 600,
        ]);

        Cache::forget('fansku:payment-methods');
        Http::fake([
            'api.fansku.id/api/v1/public-api/payment-methods' => Http::response([
                'success' => true,
                'data' => [[
                    'slug' => 'pembayaran-qris',
                    'payment_methods' => [['slug' => 'qris', 'name' => 'QRIS', 'is_active' => true]],
                ]],
            ]),
            'api.fansku.id/api/v1/public-api/supports' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 'SUP-001',
                    'amount' => 50000,
                    'fee' => 300,
                    'total_amount' => 50300,
                    'payment' => ['actions' => [
                        ['type' => 'PRESENT_TO_CUSTOMER', 'value' => 'qr-abc', 'descriptor' => 'QR_STRING'],
                    ]],
                ],
            ]),
        ]);

        $this->loginAs();

        $response = $this->postJson('/api/checkout/fansku', [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => 'other@example.com',
        ]);

        $response->assertOk();
        $this->assertSame('buyer@example.com', Transaction::first()->customer_contact);
        Cache::forget('fansku:payment-methods');
    }

    // -------------------------------------------------------
    // Isolasi antar user: success, invoice, QRIS, detail API
    // -------------------------------------------------------

    public function test_user_cannot_see_other_users_order_pages(): void
    {
        $tx = $this->makeTransaction([
            'customer_contact' => 'other@example.com',
            'fansku_support_id' => 'SUP-001',
            'fansku_status' => 'pending',
            'fansku_raw_response' => ['fee' => 0, 'total_amount' => 50000],
        ]);

        $this->loginAs();

        $this->get('/success/' . $tx->code)->assertStatus(404);
        $this->get('/invoices/' . $tx->code)->assertStatus(404);
        $this->get('/bayar/qris/' . $tx->code)->assertStatus(404);
        $this->getJson('/api/checkout/fansku/' . $tx->code)->assertStatus(404);
        $this->get('/orders/' . $tx->code)->assertStatus(404);
        $this->get('/riwayat/' . $tx->code)->assertStatus(404);
    }

    public function test_user_can_see_own_success_invoice_and_qris(): void
    {
        $this->loginAs();
        $tx = $this->makeTransaction([
            'fansku_support_id' => 'SUP-001',
            'fansku_status' => 'pending',
            'fansku_raw_response' => ['fee' => 0, 'total_amount' => 50000],
        ]);

        $this->get('/success/' . $tx->code)->assertOk();
        $this->get('/invoices/' . $tx->code)->assertOk();
        $this->get('/bayar/qris/' . $tx->code)->assertOk();
        $this->getJson('/api/checkout/fansku/' . $tx->code)->assertOk();
    }

    // -------------------------------------------------------
    // Halaman orders: hanya milik sendiri, WA lama disembunyikan
    // -------------------------------------------------------

    public function test_orders_page_shows_only_own_and_hides_legacy_wa(): void
    {
        $this->loginAs();

        $this->makeTransaction(['code' => 'KURON-INV-20260101-MINE']);
        $this->makeTransaction(['code' => 'KURON-INV-20260101-WA', 'customer_contact' => '08123456789']);
        $this->makeTransaction(['code' => 'KURON-INV-20260101-OTHER', 'customer_contact' => 'other@example.com']);

        $response = $this->get('/orders');

        $response->assertOk();
        $response->assertSee('KURON-INV-20260101-MINE');
        $response->assertDontSee('KURON-INV-20260101-WA');
        $response->assertDontSee('KURON-INV-20260101-OTHER');
    }

    // -------------------------------------------------------
    // Endpoint publik tetap publik
    // -------------------------------------------------------

    public function test_public_catalog_endpoints_stay_public(): void
    {
        $this->getJson('/api/config')->assertOk();
        $this->getJson('/api/plans')->assertOk();
        $this->getJson('/api/faqs')->assertOk();
        $this->getJson('/api/payment-methods')->assertOk();
    }

    // -------------------------------------------------------
    // Mobile: tukar Google ID token -> Sanctum token
    // -------------------------------------------------------

    private string $googleKid = 'test-kid-1';

    private string $googlePrivateKey;

    private function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    private function setupGoogleJwks(): void
    {
        Cache::forget('google:jwks');
        config(['services.google.client_id' => 'test-client-id.apps.googleusercontent.com']);

        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($res, $privatePem);
        $this->googlePrivateKey = $privatePem;

        $details = openssl_pkey_get_details($res);

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'keys' => [[
                    'kty' => 'RSA',
                    'kid' => $this->googleKid,
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => $this->base64Url($details['rsa']['n']),
                    'e' => $this->base64Url($details['rsa']['e']),
                ]],
            ]),
        ]);
    }

    private function makeIdToken(array $overrides = []): string
    {
        $payload = array_merge([
            'iss' => 'https://accounts.google.com',
            'aud' => 'test-client-id.apps.googleusercontent.com',
            'sub' => 'google-mobile-123',
            'email' => 'mobile@example.com',
            'email_verified' => true,
            'name' => 'Mobile User',
            'picture' => 'https://example.com/pic.jpg',
            'exp' => time() + 3600,
            'iat' => time(),
        ], $overrides);

        return JWT::encode($payload, $this->googlePrivateKey, 'RS256', $this->googleKid);
    }

    public function test_mobile_google_login_rejects_missing_token(): void
    {
        $this->postJson('/api/auth/google/mobile', [])
            ->assertStatus(422);
    }

    public function test_mobile_google_login_rejects_invalid_token(): void
    {
        $this->setupGoogleJwks();

        $this->postJson('/api/auth/google/mobile', ['id_token' => 'not-a-jwt'])
            ->assertStatus(401);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_mobile_google_login_rejects_wrong_audience(): void
    {
        $this->setupGoogleJwks();

        $token = $this->makeIdToken(['aud' => 'other-client.apps.googleusercontent.com']);

        $this->postJson('/api/auth/google/mobile', ['id_token' => $token])
            ->assertStatus(401);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_mobile_google_login_rejects_unverified_email(): void
    {
        $this->setupGoogleJwks();

        $token = $this->makeIdToken(['email_verified' => false]);

        $this->postJson('/api/auth/google/mobile', ['id_token' => $token])
            ->assertStatus(401);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_mobile_google_login_rejects_bad_issuer(): void
    {
        $this->setupGoogleJwks();

        $token = $this->makeIdToken(['iss' => 'https://evil.example.com']);

        $this->postJson('/api/auth/google/mobile', ['id_token' => $token])
            ->assertStatus(401);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_mobile_google_login_returns_sanctum_token_and_reuses_user(): void
    {
        $this->setupGoogleJwks();

        $token = $this->makeIdToken();

        $response = $this->postJson('/api/auth/google/mobile', ['id_token' => $token]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'mobile@example.com');

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('users', [
            'email' => 'mobile@example.com',
            'google_id' => 'google-mobile-123',
        ]);

        // Login ulang dengan token yang sama tidak membuat user duplikat.
        $this->postJson('/api/auth/google/mobile', ['id_token' => $token])->assertOk();
        $this->assertDatabaseCount('users', 1);
    }

    // -------------------------------------------------------
    // Mobile checkout via Sanctum Bearer token
    // -------------------------------------------------------

    public function test_mobile_checkout_requires_sanctum_token(): void
    {
        $payload = [
            'plan_name' => 'Starter',
            'device_quota' => 1,
            'duration_months' => 1,
            'customer_contact' => 'mobile@example.com',
            'payment_method' => 'BRIVA',
        ];

        // Tanpa token → 401.
        $this->postJson('/api/checkout', $payload)->assertStatus(401);

        // Dengan Bearer token → sukses, kontak dikunci ke pemilik token.
        $user = User::factory()->create(['email' => 'mobile@example.com']);
        $bearer = $user->createToken('mobile')->plainTextToken;

        $response = $this->postJson('/api/checkout', array_merge($payload, [
            'customer_contact' => 'other@example.com',
        ]), ['Authorization' => 'Bearer ' . $bearer]);

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('transactions', ['customer_contact' => 'mobile@example.com']);
        $this->assertDatabaseMissing('transactions', ['customer_contact' => 'other@example.com']);
    }
}
