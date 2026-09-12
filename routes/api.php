<?php

use Illuminate\Support\Facades\Route;

Route::get('/user', [\App\Http\Controllers\ApiController::class, 'me'])->middleware('auth:sanctum');

Route::get('/config', [\App\Http\Controllers\ApiController::class, 'config']);
Route::get('/plans', [\App\Http\Controllers\ApiController::class, 'plans']);
Route::get('/faqs', [\App\Http\Controllers\ApiController::class, 'faqs']);
Route::get('/payment-methods', [\App\Http\Controllers\ApiController::class, 'paymentMethods']);

Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])
    ->middleware('throttle:' . config('tripay.rate_limit.checkout', '5,10'));

// Dedicated QRIS Otomatis (Fansku) checkout — strict, no silent fallback.
Route::post('/checkout/fansku', [\App\Http\Controllers\CheckoutController::class, 'storeFansku'])
    ->middleware('throttle:' . config('tripay.rate_limit.checkout', '5,10'));

// QRIS payment status (backs the reload-safe /bayar/qris/{code} page + polling).
Route::get('/checkout/fansku/{transaction:code}', [\App\Http\Controllers\CheckoutController::class, 'showFansku']);
Route::post('/check-voucher', [\App\Http\Controllers\ApiController::class, 'checkVoucher']);
Route::post('/check-license', [\App\Http\Controllers\LicenseController::class, 'check']);
Route::post('/error-report', [\App\Http\Controllers\Api\ErrorReportController::class, 'store']);
Route::get('/check-update', [\App\Http\Controllers\Api\VersionController::class, 'check']);

// Katalog v2 (proxy resmi komiktap.info, tanpa kata "scrape")
Route::prefix('v2/catalog')
    ->middleware('throttle:' . config('catalog.rate_limit', '60,1'))
    ->group(function () {
        Route::get('/comics', [\App\Http\Controllers\Api\CatalogController::class, 'comics']);
        Route::get('/comics/{id}', [\App\Http\Controllers\Api\CatalogController::class, 'show'])
            ->where('id', '[a-z0-9][a-z0-9-]*');
        Route::get('/comics/{id}/chapters/{number}', [\App\Http\Controllers\Api\CatalogController::class, 'chapter'])
            ->where('id', '[a-z0-9][a-z0-9-]*')
            ->where('number', '[0-9]+(?:\.[0-9]+)?');
        Route::get('/announcements', [\App\Http\Controllers\Api\CatalogController::class, 'announcements']);
        Route::get('/image', [\App\Http\Controllers\Api\CatalogController::class, 'image']);
        Route::get('/genres/{slug}', [\App\Http\Controllers\Api\CatalogController::class, 'byGenre'])
            ->where('slug', '[a-z0-9][a-z0-9-]*');
        Route::get('/genres', [\App\Http\Controllers\Api\CatalogController::class, 'genres']);
        Route::get('/az', [\App\Http\Controllers\Api\CatalogController::class, 'az']);
    });
