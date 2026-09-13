<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class PurchaseHistoryController extends Controller
{
    /**
     * Daftar transaksi milik email pengguna yang login.
     */
    public function index(Request $request)
    {
        $transactions = Transaction::where('customer_contact', $request->user()->email)
            ->orderByDesc('created_at')
            ->get();

        return view('history.index', compact('transactions'));
    }

    /**
     * Detail transaksi milik sendiri; milik orang lain → 404.
     */
    public function show(Request $request, string $code)
    {
        $transaction = Transaction::where('code', $code)
            ->where('customer_contact', $request->user()->email)
            ->firstOrFail();

        // Pending + pembayaran otomatis (Fansku) → siapkan data QR dari server
        // agar pengguna bisa scan ulang / cek ulang status dari halaman riwayat.
        $qrString = null;
        $fee = 0;
        $total = (int) $transaction->amount;

        if ($transaction->status === 'pending' && $transaction->fansku_support_id) {
            $raw = $transaction->fansku_raw_response ?? [];
            if (! is_array($raw)) {
                $raw = [];
            }
            $qrString = app(\App\Services\FanskuService::class)->extractQrString($raw);
            $fee = (int) ($raw['fee'] ?? 0);
            $total = (int) ($raw['total_amount'] ?? $transaction->amount);
        }

        return view('history.show', compact('transaction', 'qrString', 'fee', 'total'));
    }
}
