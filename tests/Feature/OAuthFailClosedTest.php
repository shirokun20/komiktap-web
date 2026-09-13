<?php

namespace Tests\Feature;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class OAuthFailClosedTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_login_rejects_missing_email_verified_flag(): void
    {
        $user = new SocialiteUser;
        $user->map([
            'id' => 'google-123',
            'name' => 'No Flag',
            'email' => 'noflag@example.com',
            'avatar' => null,
        ]);
        // Sengaja tanpa kunci email_verified / verified_email.
        $user->setRaw(['sub' => 'google-123', 'email' => 'noflag@example.com']);

        Socialite::fake('google', $user);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_mobile_login_rejects_unconfigured_client_id(): void
    {
        config(['services.google.client_id' => null]);
        $privateKey = $this->fakeGoogleJwks();

        $idToken = $this->mintIdToken($privateKey, [
            'aud' => 'any-client-at-all',
            'sub' => 'google-999',
            'email' => 'victim@example.com',
        ]);

        $this->postJson('/api/auth/google/mobile', ['id_token' => $idToken])
            ->assertStatus(401);
        $this->assertDatabaseMissing('users', ['email' => 'victim@example.com']);
    }

    public function test_mobile_login_rejects_foreign_audience(): void
    {
        config(['services.google.client_id' => 'our-client-id']);
        $privateKey = $this->fakeGoogleJwks();

        $idToken = $this->mintIdToken($privateKey, [
            'aud' => 'foreign-client-id',
            'sub' => 'google-999',
            'email' => 'victim@example.com',
        ]);

        $this->postJson('/api/auth/google/mobile', ['id_token' => $idToken])
            ->assertStatus(401);
        $this->assertDatabaseMissing('users', ['email' => 'victim@example.com']);
    }

    public function test_mobile_login_accepts_matching_audience(): void
    {
        config(['services.google.client_id' => 'our-client-id']);
        $privateKey = $this->fakeGoogleJwks();

        $idToken = $this->mintIdToken($privateKey, [
            'aud' => 'our-client-id',
            'sub' => 'google-123',
            'email' => 'buyer@example.com',
        ]);

        $this->postJson('/api/auth/google/mobile', ['id_token' => $idToken])
            ->assertOk()
            ->assertJsonPath('data.user.email', 'buyer@example.com');
        $this->assertDatabaseHas('users', ['email' => 'buyer@example.com']);
    }

    /**
     * @return string PEM private key untuk menandatangani token uji.
     */
    private function fakeGoogleJwks(): string
    {
        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($res, $privateKey);
        $details = openssl_pkey_get_details($res);
        $b64url = fn (string $bin): string => rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');

        Http::fake([
            'www.googleapis.com/*' => Http::response(['keys' => [[
                'kty' => 'RSA',
                'kid' => 'test-key',
                'alg' => 'RS256',
                'use' => 'sig',
                'n' => $b64url($details['rsa']['n']),
                'e' => $b64url($details['rsa']['e']),
            ]]], 200),
        ]);

        return $privateKey;
    }

    private function mintIdToken(string $privateKey, array $claims): string
    {
        return JWT::encode(array_merge([
            'iss' => 'https://accounts.google.com',
            'email_verified' => true,
            'name' => 'Test User',
            'exp' => now()->addHour()->timestamp,
            'iat' => now()->timestamp,
        ], $claims), $privateKey, 'RS256', 'test-key');
    }
}
