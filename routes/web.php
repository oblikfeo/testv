<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionKeyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

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
