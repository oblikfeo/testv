<?php

use App\Http\Controllers\CabinetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionKeyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/agreement', 'agreement')->name('agreement');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/offer', 'offer')->name('offer');

Route::get('/dashboard', function () {
    return redirect()->route('cabinet.subscription');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/cabinet', [CabinetController::class, 'subscription'])->name('cabinet.subscription');
    Route::get('/cabinet/trial', [CabinetController::class, 'trial'])->name('cabinet.trial');
    Route::get('/cabinet/profile', [CabinetController::class, 'profile'])->name('cabinet.profile');
    Route::get('/cabinet/security', [CabinetController::class, 'security'])->name('cabinet.security');
    Route::get('/cabinet/history', [CabinetController::class, 'history'])->name('cabinet.history');
});

Route::middleware('auth')->group(function () {
    Route::get('/keys', [SubscriptionKeyController::class, 'index'])->name('keys.index');
    Route::post('/keys/issue', [SubscriptionKeyController::class, 'issue'])->name('keys.issue');
    Route::post('/keys/{subscription_key}/activate', [SubscriptionKeyController::class, 'activate'])
        ->name('keys.activate');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
