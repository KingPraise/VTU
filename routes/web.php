<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Group routes that require authentication and 2FA
Route::group(['middleware' => ['auth', '2fa']], function () {
    Route::get('/', [HomeController::class, 'home']); // Home route
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard'); // User dashboard route

    // Routes for airtime purchase
    Route::get('/airtime-top-up', [TransactionController::class, 'airtimeTopUpForm'])->name('airtime-form');
    Route::post('/airtime-top-up', [TransactionController::class, 'processAirtimeTopUp'])->name('transaction.airtime');
    Route::get('/transaction-history', [TransactionController::class, 'transactionHistory'])->name('transaction.history');

    // Wallet and transaction routes
    Route::get('/wallet/top-up', [WalletController::class, 'showTopUpForm'])->name('wallet.topup');
    Route::post('/wallet/top-up', [WalletController::class, 'processTopUp'])->name('wallet.processTopUp');
    Route::get('/wallet/callback', [WalletController::class, 'handleCallback'])->name('wallet.callback');

    // Routes for data bundle purchase
    Route::get('/data-bundle-purchase', [TransactionController::class, 'dataBundleForm'])->name('data-bundle-form');
    Route::post('/data-bundle-purchase', [TransactionController::class, 'processDataBundlePurchase'])->name('transaction.data-bundle');

    // Routes for generic service transactions (airtime/data) and handling Paystack callback
    Route::post('/service/transaction', [ServiceController::class, 'processTransaction'])->name('service.transaction');
    Route::get('/paystack/callback', [ServiceController::class, 'handlePaystackCallback'])->name('paystack.callback');
    Route::get('/transaction-history', [TransactionController::class, 'transactionHistory'])->name('transaction.history');

    // User management
    Route::resource('/users', AdminUserController::class)->except(['show']);

    // Other pages
    Route::get('/profile', function () {return view('profile');})->name('profile');
    Route::get('/billing', function () {return view('billing');})->name('billing');
    Route::get('/user-management', function () {return view('laravel-examples/user-management');})->name('user-management');
    Route::get('/user-profile', function () {return view('profile/user-profile');})->name('user-profile');

    // Logout route (now as a POST request)
    Route::post('/logout', [SessionsController::class, 'destroy'])->name('logout');
});

// Guest routes (for unauthenticated users)
Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [SessionsController::class, 'create'])->name('login');
    Route::post('/session', [SessionsController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login/forgot-password', [ResetController::class, 'create']);
    Route::post('/forgot-password', [ResetController::class, 'showResetForm']);
    Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});

// Routes for 2FA
Route::get('/2fa', [TwoFactorController::class, 'show'])->name('2fa.show');
Route::post('/2fa', [TwoFactorController::class, 'enable'])->name('2fa.enable');
Route::get('/2fa/verify', [TwoFactorController::class, 'verifyIndex'])->name('2fa.verify');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post');