<?php

use App\Http\Controllers\CheckoutController;


use App\Models\Plan;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/checkout', [CheckoutController::class, 'store']);

Route::view('/contact', 'contact')->name('contact');
Route::view('/dmca', 'dmca')->name('dmca');

Route::get('/invoices/{transaction:code}', function (\App\Models\Transaction $transaction) {
    return view('invoices.show', compact('transaction'));
})->name('invoices.show');
