<?php

use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\SubscriptionStatusApiController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/sub/{subId}', [SubscriptionController::class, 'show'])->name('api.subscription.show');

Route::middleware(['api.token'])->group(function (): void {
    Route::post('/device/register', [DeviceApiController::class, 'register'])->name('api.device.register');
    Route::get('/device/validate', [DeviceApiController::class, 'validateDevice'])->name('api.device.validate');
    Route::get('/subscription/status', [SubscriptionStatusApiController::class, 'show'])->name('api.subscription.status');
});
