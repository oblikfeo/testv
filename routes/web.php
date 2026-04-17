<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionKeyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/offer', 'offer')->name('offer');
Route::view('/personal-data', 'personal-data')->name('personal-data');

Route::get('/dashboard', function () {
    return redirect()->route('cabinet.subscription');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/cabinet', [CabinetController::class, 'subscription'])->name('cabinet.subscription');
    Route::get('/cabinet/devices', [CabinetController::class, 'devices'])->name('cabinet.devices');
    Route::delete('/cabinet/devices/{device}', [DeviceController::class, 'destroy'])->name('cabinet.devices.destroy');
    Route::get('/cabinet/trial', [CabinetController::class, 'trial'])->name('cabinet.trial');
    Route::post('/cabinet/trial', [CabinetController::class, 'createTrial'])->name('cabinet.trial.create');
    Route::get('/cabinet/profile', [CabinetController::class, 'profile'])->name('cabinet.profile');
    Route::get('/cabinet/security', [CabinetController::class, 'security'])->name('cabinet.security');
    Route::get('/cabinet/history', [CabinetController::class, 'history'])->name('cabinet.history');

    Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
    Route::get('/payment/status', [PaymentController::class, 'checkStatus'])->name('payment.status');
});

// Subscription endpoint (public, no auth required)
Route::get('/sub/{subId}', [SubscriptionController::class, 'show'])->name('subscription.show');

// YooKassa webhook (public)
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/keys', [SubscriptionKeyController::class, 'index'])->name('keys.index');
    Route::post('/keys/issue', [SubscriptionKeyController::class, 'issue'])->name('keys.issue');
    Route::post('/keys/{subscription_key}/activate', [SubscriptionKeyController::class, 'activate'])
        ->name('keys.activate');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'authenticate'])->name('admin.authenticate');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::middleware(\App\Http\Middleware\AdminAuth::class)->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/test-keys', [AdminController::class, 'testKeys'])->name('admin.test-keys');
        Route::post('/test-keys/create', [AdminController::class, 'createTestKey'])->name('admin.test-keys.create');
        Route::post('/test-keys/delete', [AdminController::class, 'deleteTestKey'])->name('admin.test-keys.delete');
    });
});

require __DIR__.'/auth.php';
