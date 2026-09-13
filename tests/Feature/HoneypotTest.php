<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Transaction;
use App\Settings\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoneypotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $settings = app(PaymentSettings::class);
        $settings->is_enabled      = true;
        $settings->payment_methods = [
            [
                'name'            => 'BRIVA',
                'account_number'  => '1234567890',
                'account_holder'  => 'KomikTap',
                'usage_type'      => 'all',
                'instructions'    => '',
                'qris_image_path' => null,
                'qris_string'     => null,
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

    // -------------------------------------------------------
    // 9.11 Honeypot on checkout — filled → fake success, no transaction
    // -------------------------------------------------------

    public function test_checkout_with_filled_honeypot_returns_fake_success_without_creating_transaction(): void
    {
        $user = \App\Models\User::factory()->create(['email' => 'bot@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/api/checkout', [
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'customer_contact' => 'bot@example.com',
            'payment_method'   => 'BRIVA',
            '_hp_website'      => 'http://spam.com', // honeypot filled
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // No real transaction should be created
        $this->assertDatabaseMissing('transactions', ['customer_contact' => 'bot@example.com']);
    }

    // -------------------------------------------------------
    // 9.11 Honeypot on checkout — empty → normal processing
    // -------------------------------------------------------

    public function test_checkout_with_empty_honeypot_processes_normally(): void
    {
        $user = \App\Models\User::factory()->create(['email' => 'human@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/api/checkout', [
            'plan_name'        => 'Starter',
            'device_quota'     => 1,
            'duration_months'  => 1,
            'customer_contact' => 'human@example.com',
            'payment_method'   => 'BRIVA',
            '_hp_website'      => '', // honeypot empty
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('transactions', ['customer_contact' => 'human@example.com']);
    }

    // -------------------------------------------------------
    // 9.12 Honeypot on contact form — filled → fake success, no message
    // -------------------------------------------------------

    public function test_contact_with_filled_honeypot_returns_fake_success_without_storing_message(): void
    {
        $response = $this->postJson('/contact/send', [
            'name'         => 'Bot',
            'email'        => 'bot@spam.com',
            'message'      => 'Buy cheap meds',
            '_hp_website'  => 'http://spam.com', // honeypot filled
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('messages', ['email' => 'bot@spam.com']);
    }

    // -------------------------------------------------------
    // 9.12 Honeypot on contact form — empty → message stored
    // -------------------------------------------------------

    public function test_contact_with_empty_honeypot_stores_message(): void
    {
        $response = $this->postJson('/contact/send', [
            'name'        => 'Human',
            'email'       => 'human@example.com',
            'message'     => 'Hello, I need help.',
            '_hp_website' => '', // honeypot empty
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $this->assertDatabaseHas('messages', ['email' => 'human@example.com']);
    }
}
