<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;

use App\Models\Plan;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/checkout', [CheckoutController::class, 'store']);

Route::get('/success/{transaction:code}', function (\App\Models\Transaction $transaction) {
    return view('success', compact('transaction'));
})->name('checkout.success');

Route::view('/contact', 'contact')->name('contact');
Route::post('/contact/send', [ContactController::class, 'sendMessage'])->name('contact.send');


Route::get('/invoices/{transaction:code}', function (\App\Models\Transaction $transaction) {
    return view('invoices.show', compact('transaction'));
})->name('invoices.show');

Route::get('/download', function () {
    $apkVersions = \App\Models\ApkVersion::where('is_active', true)->orderBy('created_at', 'desc')->get();
    $latestApk = $apkVersions->first();
    return view('download', compact('apkVersions', 'latestApk'));
})->name('download.index');

Route::get('/download/{apk:version_code}', function (\App\Models\ApkVersion $apk) {
    if (!$apk->is_active) {
        abort(404);
    }
    // Increment download count
    $apk->increment('download_count');
    
    return Storage::disk('public')->download($apk->file_path, 'KomikTap_v' . $apk->version_code . '.apk');
})->name('download.version');

Route::get('/download/latest', function () {
    $apk = \App\Models\ApkVersion::where('is_active', true)->latest()->firstOrFail();
    
    // Increment download count
    $apk->increment('download_count');
    
    return Storage::disk('public')->download($apk->file_path, 'KomikTap_v' . $apk->version_code . '.apk');
})->name('download.file');

Route::get('/{slug}', function ($slug) {
    if (in_array($slug, ['assets', 'storage', 'admin', 'livewire'])) {
        abort(404);
    }
    
    $page = \App\Models\Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
    return view('page', compact('page'));
})->name('page.show');
