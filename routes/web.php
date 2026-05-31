<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TripayCallbackController;
use App\Http\Controllers\OrderTrackingController;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// TriPay callback — no CSRF (called by TriPay server)
Route::post('/api/tripay/callback', [TripayCallbackController::class, 'handle'])
    ->name('tripay.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// 8.3 Checkout with rate limiting (5 per 10 minutes)
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:' . config('tripay.rate_limit.checkout', '5,10'));

Route::get('/success/{transaction:code}', function (\App\Models\Transaction $transaction) {
    return view('success', compact('transaction'));
})->name('checkout.success');

Route::view('/contact', 'contact')->name('contact');

// 8.4 Contact form with rate limiting (3 per 10 minutes)
Route::post('/contact/send', [ContactController::class, 'sendMessage'])
    ->name('contact.send')
    ->middleware('throttle:' . config('tripay.rate_limit.contact', '3,10'));

Route::get('/invoices/{transaction:code}', function (\App\Models\Transaction $transaction) {
    return view('invoices.show', compact('transaction'));
})->name('invoices.show');

Route::get('/donasi', [\App\Http\Controllers\DonationController::class, 'index'])->name('donation.index');
Route::get('/donasi/{slug}', [\App\Http\Controllers\DonationController::class, 'show'])->name('donation.show');
Route::get('/donasi/{slug}/bayar', [\App\Http\Controllers\DonationController::class, 'payment'])->name('donation.payment');

// Order Tracking (no login required)
Route::get('/orders', [OrderTrackingController::class, 'index'])->name('orders.index');

// 8.5 Order lookup with rate limiting (10 per 10 minutes)
Route::post('/orders/lookup', [OrderTrackingController::class, 'lookup'])
    ->name('orders.lookup')
    ->middleware('throttle:' . config('tripay.rate_limit.lookup', '10,10'));

Route::get('/orders/{code}', [OrderTrackingController::class, 'show'])->name('orders.show');

Route::get('/bayar', function () {
    return view('payment');
})->name('payment.index');

Route::get('/download', function () {
    $apkVersions = \App\Models\ApkVersion::where('is_active', true)->orderBy('created_at', 'desc')->get();
    $latestApk = $apkVersions->first();

    return view('download', compact('apkVersions', 'latestApk'));
})->name('download.index');

// 8.6 Generate a signed download ticket URL
Route::get('/download/ticket/{apk:version_code}', function (\App\Models\ApkVersion $apk) {
    if (! $apk->is_active) {
        abort(404);
    }

    $expires   = now()->addMinutes(30)->timestamp;
    $signature = hash_hmac('sha256', "download:{$apk->version_code}:{$expires}", config('app.key'));

    $url = route('download.version', [
        'apk'       => $apk->version_code,
        'expires'   => $expires,
        'signature' => $signature,
    ]);

    return redirect($url);
})->name('download.ticket');

// 8.7 / 8.8 Download with signed URL validation + rate limiting (10 per 10 minutes)
Route::get('/download/{apk:version_code}', function (\App\Models\ApkVersion $apk, \Illuminate\Http\Request $request) {
    if (! $apk->is_active) {
        abort(404);
    }

    $expires   = $request->query('expires');
    $signature = $request->query('signature');

    // If no signature params, redirect to download page
    if (! $expires || ! $signature) {
        return redirect()->route('download.index');
    }

    // Check expiry
    if ((int) $expires < now()->timestamp) {
        abort(410, 'Download link has expired. Please return to the download page.');
    }

    // Validate signature
    $expected = hash_hmac('sha256', "download:{$apk->version_code}:{$expires}", config('app.key'));
    if (! hash_equals($expected, $signature)) {
        abort(403, 'Invalid download signature.');
    }

    // Increment download count
    $apk->increment('download_count');

    return Storage::disk('public')->download($apk->file_path, 'KomikTap_v'.$apk->version_code.'.apk');
})->name('download.version')
  ->middleware('throttle:' . config('tripay.rate_limit.download', '10,10'));

Route::get('/download/latest', function () {
    $apk = \App\Models\ApkVersion::where('is_active', true)->latest()->firstOrFail();

    return redirect()->route('download.ticket', $apk->version_code);
})->name('download.file');

Route::redirect('/web-donasin.html', '/donasi');

Route::get('/{slug}', function ($slug) {
    if (in_array($slug, ['assets', 'storage', 'admin', 'livewire'])) {
        abort(404);
    }

    $page = \App\Models\Page::where('slug', $slug)->where('is_published', true)->firstOrFail();

    return view('page', compact('page'));
})->name('page.show');
