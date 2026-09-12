<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_configuration_page_renders_and_saves(): void
    {
        $user = User::factory()->create(['email' => 'admin@komiktap.com']);
        $this->actingAs($user);

        $this->get('/admin/payment-configuration')->assertOk();

        Livewire::test(\App\Filament\Pages\PaymentConfiguration::class)
            ->set('data.fansku_enabled', true)
            ->set('data.manual_enabled', true)
            ->set('data.is_enabled', true)
            ->set('data.payment_methods', [
                [
                    'name' => 'BCA Transfer',
                    'account_number' => '1234567890',
                    'account_holder' => 'KomikTap',
                    'usage_type' => 'all',
                    'instructions' => 'Transfer ya',
                    'qris_image_path' => null,
                    'qris_string' => null,
                    'is_active' => true,
                ],
                [
                    'name' => 'DANA Off',
                    'account_number' => '08111',
                    'account_holder' => 'KomikTap',
                    'usage_type' => 'order',
                    'instructions' => '',
                    'qris_image_path' => null,
                    'qris_string' => null,
                    'is_active' => false,
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $gateway = app(\App\Settings\PaymentGatewaySettings::class);
        $this->assertTrue($gateway->fansku_enabled);
        $this->assertTrue($gateway->manual_enabled);

        $payment = app(\App\Settings\PaymentSettings::class);
        $this->assertCount(2, $payment->payment_methods);
        $this->assertTrue($payment->payment_methods[0]['is_active']);
        $this->assertFalse($payment->payment_methods[1]['is_active']);

        // API reflects saved state: fansku flag + only active manual method.
        $this->getJson('/api/payment-methods?type=order')
            ->assertOk()
            ->assertJsonPath('data.fansku_enabled', true)
            ->assertJsonCount(1, 'data.payment_methods')
            ->assertJsonPath('data.payment_methods.0.name', 'BCA Transfer');
    }
}
