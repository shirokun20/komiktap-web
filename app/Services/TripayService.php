<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    protected string $apiKey;
    protected string $privateKey;
    protected string $merchantCode;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey       = config('tripay.api_key', '');
        $this->privateKey   = config('tripay.private_key', '');
        $this->merchantCode = config('tripay.merchant_code', '');

        $mode = config('tripay.mode', 'sandbox');
        $this->baseUrl = config("tripay.base_url.{$mode}", config('tripay.base_url.sandbox'));
    }

    /**
     * Build HMAC-SHA256 signature for create transaction request.
     */
    public function buildSignature(string $merchantRef, int $amount): string
    {
        return hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey);
    }

    /**
     * Validate incoming callback signature.
     *
     * @param string $rawBody  Raw request body string
     * @param string $signature  Value from X-Callback-Signature header
     */
    public function validateCallback(string $rawBody, string $signature): bool
    {
        $expected = hash_hmac('sha256', $rawBody, $this->privateKey);
        return hash_equals($expected, $signature);
    }

    /**
     * Create a closed payment transaction on TriPay.
     *
     * @param array $data {
     *   method: string,          // Payment channel code e.g. BRIVA, QRIS
     *   merchant_ref: string,    // Your transaction code
     *   amount: int,
     *   customer_name: string,
     *   customer_email: string,
     *   customer_phone: string,
     *   order_items: array,      // [{name, price, quantity}]
     *   expired_time?: int,      // Unix timestamp, default 24h from now
     *   return_url?: string,
     * }
     * @return array {reference, pay_code, checkout_url, expired_time, ...}
     * @throws \Exception on API error
     */
    public function createTransaction(array $data): array
    {
        if (!isset($data['expired_time'])) {
            $data['expired_time'] = now()->addHours(24)->timestamp;
        }

        $data['merchant_ref'] = $data['merchant_ref'];
        $data['signature']    = $this->buildSignature($data['merchant_ref'], (int) $data['amount']);

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/transaction/create", $data);

        $json = $response->json();

        if (! $response->successful() || ! ($json['success'] ?? false)) {
            $message = $json['message'] ?? 'TriPay API error';
            Log::error('TripayService::createTransaction failed', ['response' => $json]);
            throw new \Exception("TriPay: {$message}");
        }

        return $json['data'];
    }

    /**
     * Get payment instruction steps for a given channel code.
     *
     * @param string $code  e.g. 'BRIVA', 'QRIS'
     * @return array  Array of instruction steps
     * @throws \Exception on API error
     */
    public function getPaymentInstruction(string $code): array
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/payment/instruction", [
                'code'  => $code,
                'allow_html' => 1,
            ]);

        $json = $response->json();

        if (! $response->successful() || ! ($json['success'] ?? false)) {
            $message = $json['message'] ?? 'TriPay API error';
            Log::error('TripayService::getPaymentInstruction failed', ['code' => $code, 'response' => $json]);
            throw new \Exception("TriPay: {$message}");
        }

        return $json['data'] ?? [];
    }

    /**
     * Get available payment channels.
     *
     * @return array  Array of channels with code, name, group, type, fee, active status
     * @throws \Exception on API error
     */
    public function getPaymentChannels(): array
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/merchant/payment-channel");

        $json = $response->json();

        if (! $response->successful() || ! ($json['success'] ?? false)) {
            $message = $json['message'] ?? 'TriPay API error';
            Log::error('TripayService::getPaymentChannels failed', ['response' => $json]);
            throw new \Exception("TriPay: {$message}");
        }

        return $json['data'] ?? [];
    }

    /**
     * Get transaction detail / status from TriPay.
     *
     * @param string $reference  TriPay reference number
     * @return array  Transaction detail data
     * @throws \Exception on API error
     */
    public function getTransactionDetail(string $reference): array
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/transaction/detail", [
                'reference' => $reference,
            ]);

        $json = $response->json();

        if (! $response->successful() || ! ($json['success'] ?? false)) {
            $message = $json['message'] ?? 'TriPay API error';
            Log::error('TripayService::getTransactionDetail failed', ['reference' => $reference, 'response' => $json]);
            throw new \Exception("TriPay: {$message}");
        }

        return $json['data'] ?? [];
    }
}
