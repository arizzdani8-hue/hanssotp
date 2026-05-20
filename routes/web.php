<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Public pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/articles', [PageController::class, 'articles'])->name('articles');
Route::get('/articles/{slug}', [PageController::class, 'article'])->name('articles.show');
Route::get('/page/{slug}', [PageController::class, 'page'])->name('page.show');
Route::get('/api-docs', [PageController::class, 'apiDocs'])->name('api-docs');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login/whatsapp', [AuthController::class, 'showWhatsAppLogin'])->name('login.whatsapp');
    Route::post('/login/whatsapp/request', [AuthController::class, 'requestWhatsAppOtp'])->name('login.whatsapp.request');
    Route::post('/login/whatsapp/verify', [AuthController::class, 'verifyWhatsAppOtp'])->name('login.whatsapp.verify');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User routes
Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Deposits
    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
    Route::get('/deposits/{deposit}', [DepositController::class, 'show'])->name('deposits.show');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/status', [OrderController::class, 'checkStatus'])->name('orders.status');

    // API: operators and pricing
    Route::get('/api/operators', [OrderController::class, 'getOperators'])->name('api.operators');
    Route::get('/api/pricing', [OrderController::class, 'getPricing'])->name('api.pricing');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
});

// Webhooks (no CSRF)
Route::post('/webhooks/dompetx', [WebhookController::class, 'dompetx'])->name('webhook.dompetx');
Route::post('/webhooks/pakasir', [WebhookController::class, 'pakasir'])->name('webhook.pakasir');

// Installer routes
Route::prefix('install')->name('installer.')->middleware('installer.check')->group(function () {
    Route::get('/', [\App\Http\Controllers\Installer\InstallerController::class, 'index'])->name('index');
    Route::get('/requirements', [\App\Http\Controllers\Installer\InstallerController::class, 'checkRequirements'])->name('requirements');
    Route::get('/database', [\App\Http\Controllers\Installer\InstallerController::class, 'showDatabase'])->name('database');
    Route::post('/database', [\App\Http\Controllers\Installer\InstallerController::class, 'setupDatabase'])->name('database.store');
    Route::get('/admin', [\App\Http\Controllers\Installer\InstallerController::class, 'showAdmin'])->name('admin');
    Route::post('/admin', [\App\Http\Controllers\Installer\InstallerController::class, 'setupAdmin'])->name('admin.store');
    Route::get('/site', [\App\Http\Controllers\Installer\InstallerController::class, 'showSite'])->name('site');
    Route::post('/site', [\App\Http\Controllers\Installer\InstallerController::class, 'setupSite'])->name('site.store');
    Route::get('/complete', [\App\Http\Controllers\Installer\InstallerController::class, 'complete'])->name('complete');
});
