<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleMobileAuthController extends Controller
{
    use ApiResponse;

    /**
     * Tukar Google ID token (dari Google Sign-In SDK mobile) dengan Sanctum token.
     * Body: { "id_token": "..." }
     */
    public function login(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            $payload = $this->verifyIdToken($request->input('id_token'));
        } catch (\Exception $e) {
            return $this->error('ID token Google tidak valid: ' . $e->getMessage(), 401);
        }

        $email = $payload['email'] ?? null;
        $emailVerified = $payload['email_verified'] ?? false;
        $googleId = $payload['sub'] ?? null;

        if (empty($email) || ! $emailVerified || empty($googleId)) {
            return $this->error('Email Google tidak terverifikasi.', 401);
        }

        $user = User::where('google_id', $googleId)->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleId,
                'avatar' => $payload['picture'] ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $payload['name'] ?? explode('@', $email)[0],
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $payload['picture'] ?? null,
                'email_verified_at' => now(),
                'password' => null,
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    /**
     * Verifikasi Google ID token via JWKS resmi.
     *
     * @return array payload
     *
     * @throws \Exception
     */
    protected function verifyIdToken(string $idToken): array
    {
        $jwks = Cache::remember('google:jwks', 3600, function () {
            $res = Http::timeout(10)->get('https://www.googleapis.com/oauth2/v3/certs');
            if (! $res->successful()) {
                throw new \Exception('Gagal mengambil Google JWKS.');
            }

            return $res->json();
        });

        $keys = JWK::parseKeySet($jwks);
        $decoded = JWT::decode($idToken, $keys);
        $payload = (array) $decoded;

        $iss = $payload['iss'] ?? '';
        if (! in_array($iss, ['https://accounts.google.com', 'accounts.google.com'], true)) {
            throw new \Exception('Issuer tidak valid.');
        }

        $clientId = config('services.google.client_id');
        $aud = $payload['aud'] ?? '';
        if ($clientId && $aud !== $clientId) {
            throw new \Exception('Audience tidak cocok.');
        }

        return $payload;
    }
}
