<?php

// ALL the addresses (URLs) of the website.
// Every line: which URL  →  which controller function runs.

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Route;

// ---------- Only for guests (people who are NOT logged in) ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ---------- Only for logged-in users ("auth" sends everybody else to /login) ----------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/chart', [DashboardController::class, 'chart'])->name('chart');   // AJAX, returns JSON

    // Accounts (add and delete, both on the dashboard)
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    // Trades – full CRUD
    Route::get('/trades', [TradeController::class, 'index'])->name('trades.index');               // Read: list (+ AJAX filter)
    Route::get('/trades/create', [TradeController::class, 'create'])->name('trades.create');      // empty form
    Route::post('/trades', [TradeController::class, 'store'])->name('trades.store');              // Create: save new
    Route::get('/trades/{trade}/edit', [TradeController::class, 'edit'])->name('trades.edit');    // form with old values
    Route::put('/trades/{trade}', [TradeController::class, 'update'])->name('trades.update');     // Update: save changes
    Route::delete('/trades/{trade}', [TradeController::class, 'destroy'])->name('trades.destroy'); // Delete (AJAX)

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
