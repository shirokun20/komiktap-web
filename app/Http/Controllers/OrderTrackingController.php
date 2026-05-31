<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Traits\HoneypotTrait;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    use HoneypotTrait;

    /**
     * Show the order lookup form.
     */
    public function index()
    {
        return view('orders.index');
    }

    /**
     * Look up orders by customer contact (email or WA).
     */
    public function lookup(Request $request)
    {
        // 8.13 Honeypot check
        if ($this->isHoneypotFilled($request)) {
            // Fake success — return empty results silently
            return view('orders.index', ['transactions' => collect(), 'searched' => true, 'contact' => '']);
        }

        $request->validate([
            'contact' => 'required|string|min:5|max:255',
        ]);

        $contact = trim($request->input('contact'));

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
     * Show a single transaction detail.
     */
    public function show(string $code)
    {
        $transaction = Transaction::where('code', $code)->firstOrFail();

        return view('orders.show', compact('transaction'));
    }
}
