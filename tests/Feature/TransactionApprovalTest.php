<?php

namespace Tests\Feature;

use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\License;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransaction(string $code, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'code' => $code,
            'plan_name' => 'Starter',
            'device_quota' => 2,
            'duration_months' => 3,
            'amount' => 50000,
            'customer_contact' => 'buyer@example.com',
            'status' => 'pending',
            'payment_method' => 'BCA',
            'payment_details' => 'No: 123',
        ], $overrides));
    }

    public function test_service_approves_order_with_license(): void
    {
        $tx = $this->makeTransaction('KURON-INV-20260101-AAA1');

        $license = app(TransactionApprovalService::class)->approve($tx);

        $this->assertNotNull($license);
        $this->assertMatchesRegularExpression('/^KURON-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license->key);
        $this->assertSame('active', $license->status);
        $this->assertSame(2, $license->max_devices);
        $this->assertSame('buyer@example.com', $license->customer_contact);
        $this->assertEquals(now()->addMonths(3)->format('Y-m-d'), $license->expires_at->format('Y-m-d'));

        $tx->refresh();
        $this->assertSame('approved', $tx->status);
        $this->assertSame($license->id, $tx->license_id);
    }

    public function test_service_approves_donation_without_license(): void
    {
        $tx = $this->makeTransaction('KURON-PEDULI-20260101-BBB2', ['plan_name' => 'Donasi']);

        $license = app(TransactionApprovalService::class)->approve($tx);

        $this->assertNull($license);
        $this->assertSame('approved', $tx->refresh()->status);
        $this->assertSame(0, License::count());
    }

    public function test_service_approve_is_noop_unless_pending(): void
    {
        $tx = $this->makeTransaction('KURON-INV-20260101-CCC3', ['status' => 'approved']);

        app(TransactionApprovalService::class)->approve($tx);

        $this->assertSame(0, License::count());
        $this->assertSame('approved', $tx->refresh()->status);
    }

    public function test_service_rejects_pending_only(): void
    {
        $pending = $this->makeTransaction('KURON-INV-20260101-DDD4');
        $approved = $this->makeTransaction('KURON-INV-20260101-EEE5', ['status' => 'approved']);

        app(TransactionApprovalService::class)->reject($pending);
        app(TransactionApprovalService::class)->reject($approved);

        $this->assertSame('rejected', $pending->refresh()->status);
        $this->assertSame('approved', $approved->refresh()->status);
    }

    public function test_bulk_approve_via_table(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'admin@komiktap.com']));

        $order = $this->makeTransaction('KURON-INV-20260101-FFF6');
        $donation = $this->makeTransaction('KURON-PEDULI-20260101-GGG7', ['plan_name' => 'Donasi']);
        $already = $this->makeTransaction('KURON-INV-20260101-HHH8', ['status' => 'approved']);

        Livewire::test(ListTransactions::class)
            ->callTableBulkAction('bulk_approve', [$order, $donation, $already])
            ->assertHasNoErrors()
            ->assertNotified();

        $this->assertSame('approved', $order->refresh()->status);
        $this->assertSame('approved', $donation->refresh()->status);
        $this->assertSame('approved', $already->refresh()->status);
        $this->assertSame(1, License::count());
        $this->assertNotNull($order->refresh()->license_id);
        $this->assertNull($donation->refresh()->license_id);
    }

    public function test_bulk_reject_via_table(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'admin@komiktap.com']));

        $pending = $this->makeTransaction('KURON-INV-20260912-III9');
        $approved = $this->makeTransaction('KURON-INV-20260912-JJJ0', ['status' => 'approved']);

        Livewire::test(ListTransactions::class)
            ->callTableBulkAction('bulk_reject', [$pending, $approved])
            ->assertHasNoErrors()
            ->assertNotified();

        $this->assertSame('rejected', $pending->refresh()->status);
        $this->assertSame('approved', $approved->refresh()->status);
    }
}
