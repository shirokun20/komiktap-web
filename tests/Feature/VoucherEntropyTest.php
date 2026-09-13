<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VoucherEntropyTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_voucher_code_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'admin@komiktap.com']));

        Livewire::test(\App\Filament\Resources\VoucherResource\Pages\CreateVoucher::class)
            ->fillForm([
                'code' => 'SHORT',
                'type' => 'fixed',
                'amount' => 10000,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['code']);

        $this->assertDatabaseMissing('vouchers', ['code' => 'SHORT']);
    }

    public function test_valid_voucher_code_is_accepted(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'admin@komiktap.com']));

        Livewire::test(\App\Filament\Resources\VoucherResource\Pages\CreateVoucher::class)
            ->fillForm([
                'code' => 'SUMMER99',
                'type' => 'fixed',
                'amount' => 10000,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vouchers', ['code' => 'SUMMER99']);
    }
}
