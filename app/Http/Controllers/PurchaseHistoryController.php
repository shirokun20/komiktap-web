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

        return view('history.show', compact('transaction'));
    }
}
