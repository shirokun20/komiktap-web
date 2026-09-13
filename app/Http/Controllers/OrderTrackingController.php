<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Traits\HoneypotTrait;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    use HoneypotTrait;

    /**
     * Show the order lookup form (wajib login; otomatis milik email login).
     */
    public function index(Request $request)
    {
        $contact = $request->user()->email;

        $transactions = Transaction::where('customer_contact', $contact)
            ->orderByDesc('created_at')
            ->get();

        return view('orders.index', [
            'transactions' => $transactions,
            'searched'     => true,
            'contact'      => $contact,
        ]);
    }

    /**
     * Look up orders — dikunci ke email login (input manual diabaikan).
     */
    public function lookup(Request $request)
    {
        $contact = $request->user()->email;

        // 8.13 Honeypot check
        if ($this->isHoneypotFilled($request)) {
            // Fake success — return empty results silently
            return view('orders.index', ['transactions' => collect(), 'searched' => true, 'contact' => '']);
        }

        $transactions = Transaction::where('customer_contact', $contact)
            ->orderByDesc('created_at')
            ->get();

        return view('orders.index', [
            'transactions' => $transactions,
            'searched'     => true,
            'contact'      => $contact,
        ]);
    }

    /**
     * Show a single transaction detail (hanya milik email login).
     */
    public function show(Request $request, string $code)
    {
        $transaction = Transaction::where('code', $code)
            ->where('customer_contact', $request->user()->email)
            ->firstOrFail();

        return view('orders.show', compact('transaction'));
    }
}
