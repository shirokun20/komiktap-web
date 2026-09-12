<?php

namespace App\Services;

use App\Models\License;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionApprovalService
{
    /**
     * Approve a pending transaction.
     *
     * Orders get an auto-generated license key; donations
     * (KURON-PEDULI-*) are approved without a license.
     * No-op unless the transaction is pending.
     *
     * @return License|null the generated license, or null for donations/skipped records.
     */
    public function approve(Transaction $transaction): ?License
    {
        if ($transaction->status !== 'pending') {
            return $transaction->license;
        }

        if (str_starts_with($transaction->code, 'KURON-PEDULI')) {
            $transaction->update([
                'status' => 'approved',
            ]);

            return null;
        }

        // Generate License with "KURON" pattern: KURON-XXXX-XXXX-XXXX
        $random = strtoupper(Str::random(12));
        $key = 'KURON-' . implode('-', str_split($random, 4));

        $license = License::create([
            'key' => $key,
            'status' => 'active',
            'max_devices' => $transaction->device_quota,
            'expires_at' => now()->addMonths($transaction->duration_months),
            'customer_contact' => $transaction->customer_contact,
        ]);

        $transaction->update([
            'status' => 'approved',
            'license_id' => $license->id,
        ]);

        return $license;
    }

    /**
     * Reject a pending transaction. No-op unless pending.
     */
    public function reject(Transaction $transaction): void
    {
        if ($transaction->status !== 'pending') {
            return;
        }

        $transaction->update(['status' => 'rejected']);
    }
}
