<?php

use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\SubscriptionStatusApiController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/sub/{subId}', [SubscriptionController::class, 'show'])->name('api.subscription.show');

/** Регистрация HWID по sub_id из подписки — без токена (секрет = sub_id в URL). */
Route::middleware(['throttle:120,1'])->group(function (): void {
    Route::post('/device/register', [DeviceApiController::class, 'register'])->name('api.device.register');
    Route::get('/device/validate', [DeviceApiController::class, 'validateDevice'])->name('api.device.validate');
});

Route::middleware(['api.token'])->group(function (): void {
    Route::get('/subscription/status', [SubscriptionStatusApiController::class, 'show'])->name('api.subscription.status');
});
