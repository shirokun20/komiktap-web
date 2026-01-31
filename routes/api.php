<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', [\App\Http\Controllers\ApiController::class, 'me'])->middleware('auth:sanctum');

Route::get('/config', [\App\Http\Controllers\ApiController::class, 'config']);
Route::get('/plans', [\App\Http\Controllers\ApiController::class, 'plans']);
Route::get('/faqs', [\App\Http\Controllers\ApiController::class, 'faqs']);
Route::get('/payment-methods', [\App\Http\Controllers\ApiController::class, 'paymentMethods']);

Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store']);
Route::post('/check-license', [\App\Http\Controllers\LicenseController::class, 'check']);
Route::post('/error-report', [\App\Http\Controllers\Api\ErrorReportController::class, 'store']);
