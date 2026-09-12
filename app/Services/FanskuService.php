<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FanskuService
{
    protected string $apiKey;
    protected string $webhookSecret;
    protected string $baseUrl;
    protected string $paymentMethod;
    protected int $methodsCacheTtl;

    public function __construct()
    {
        $this->apiKey = config('fansku.api_key', '');
        $this->webhookSecret = config('fansku.webhook_secret', '');
        $this->baseUrl = rtrim(config('fansku.base_url', 'https://api.fansku.id/api/v1/public-api'), '/');
        $this->paymentMethod = config('fansku.payment_method', 'qris');
        $this->methodsCacheTtl = (int) config('fansku.methods_cache_ttl', 600);
    }

    /**
     * Create a new QRIS support on Fansku.
     *
     * @param array $data {
     *   name: string,
     *   email: string,
     *   message: string,
     *   amount: int,
     *   payment_method?: string (must be qris),
     *   is_anonymous?: bool,
     * }
     * @return array {support_id, qr_string, amount, fee, total_amount, raw}
     *
     * @throws \Exception on validation or API error
     */
    public function createSupport(array $data): array
    {
        $method = strtolower($data['payment_method'] ?? $this->paymentMethod);

        if ($method !== 'qris') {
            throw new \InvalidArgumentException('Fansku: only qris payment method is supported.');
        }

        $payload = [
            'name' => $data['name'] ?? 'KomikTap Customer',
            'email' => $data['email'] ?? null,
            'message' => $data['message'] ?? 'KomikTap Payment',
            'amount' => (int) ($data['amount'] ?? 0),
            'payment_method' => 'qris',
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
        ];

        if (empty($payload['email']) || ! str_contains($payload['email'], '@')) {
            throw new \InvalidArgumentException('Fansku: valid email is required.');
        }

        if ($payload['amount'] < 1) {
            throw new \InvalidArgumentException('Fansku: amount must be at least 1.');
        }

        $response = Http::withHeaders([
            'key' => $this->apiKey,
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/supports", $payload);

        $json = $response->json();

        if (! $response->successful() || ! ($json['success'] ?? false)) {
            $message = $json['message'] ?? 'Fansku API error';
            Log::error('FanskuService::createSupport failed', ['response' => $json]);
            throw new \Exception("Fansku: {$message}");
        }

        $support = $json['data'] ?? [];

        return [
            'support_id' => $support['id'] ?? null,
            'qr_string' => $this->extractQrString($support),
            'amount' => $support['amount'] ?? $payload['amount'],
            'fee' => $support['fee'] ?? 0,
            'total_amount' => $support['total_amount'] ?? $payload['amount'],
            'raw' => $support,
        ];
    }

    /**
     * Extract QR string from support payment actions.
     */
    protected function extractQrString(array $support): ?string
    {
        $actions = $support['payment']['actions'] ?? [];

        foreach ($actions as $action) {
            $descriptor = strtoupper((string) ($action['descriptor'] ?? ''));
            if (in_array($descriptor, ['QR_STRING', 'QR_CODE', 'QRIS_STRING'], true)) {
                return $action['value'] ?? null;
            }
        }

        // Fallback: first PRESENT_TO_CUSTOMER action
        foreach ($actions as $action) {
            if (strtoupper((string) ($action['type'] ?? '')) === 'PRESENT_TO_CUSTOMER') {
                return $action['value'] ?? null;
            }
        }

        return null;
    }

    /**
     * Get available payment methods grouped by channel.
     *
     * @return array
     *
     * @throws \Exception on API error
     */
    public function getPaymentMethods(): array
    {
        $response = Http::withHeaders([
            'key' => $this->apiKey,
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}/payment-methods");

        $json = $response->json();

        if (! $response->successful() || ! ($json['success'] ?? false)) {
            $message = $json['message'] ?? 'Fansku API error';
            Log::error('FanskuService::getPaymentMethods failed', ['response' => $json]);
            throw new \Exception("Fansku: {$message}");
        }

        return $json['data'] ?? [];
    }

    /**
     * Check whether QRIS is active. Cached briefly to avoid
     * adding API latency to every checkout. Returns false on
     * any API failure so checkout falls back to manual.
     */
    public function isQrisActive(): bool
    {
        try {
            $groups = Cache::remember(
                'fansku:payment-methods',
                $this->methodsCacheTtl,
                fn () => $this->getPaymentMethods()
            );

            foreach ($groups as $group) {
                foreach ($group['payment_methods'] ?? [] as $method) {
                    if (strtolower((string) ($method['slug'] ?? '')) === 'qris') {
                        return (bool) ($method['is_active'] ?? false);
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::warning('FanskuService::isQrisActive fallback to false', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate incoming webhook signature (HMAC-SHA256 hex of raw body).
     */
    public function validateWebhook(string $rawBody, string $signature): bool
    {
        if ($signature === '' || $this->webhookSecret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}
