<?php

use App\Http\Controllers\Api\ResellerController;
use Illuminate\Support\Facades\Route;

// Reseller API v1 - HMAC Authentication
Route::prefix('v1')->middleware(['hmac.verify', 'throttle:api'])->group(function () {
    Route::get('/balance', [ResellerController::class, 'balance']);
    Route::get('/countries', [ResellerController::class, 'countries']);
    Route::get('/services', [ResellerController::class, 'services']);
    Route::get('/pricing', [ResellerController::class, 'pricing']);
    Route::post('/order', [ResellerController::class, 'order']);
    Route::get('/order/{orderId}', [ResellerController::class, 'checkOrder']);
    Route::post('/order/{orderId}/cancel', [ResellerController::class, 'cancelOrder']);
});
