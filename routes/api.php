<?php

use App\Http\Controllers\Api\BotApiController;
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

    Route::prefix('bot')->group(function (): void {
        Route::post('user', [BotApiController::class, 'ensureUser'])->name('api.bot.user');
        Route::get('subscription', [BotApiController::class, 'getSubscription'])->name('api.bot.subscription');
        Route::post('trial', [BotApiController::class, 'issueTrial'])->name('api.bot.trial');

        // Платежи из бота через YooKassa.
        Route::get('plans', [BotApiController::class, 'listPlans'])->name('api.bot.plans');
        Route::post('payment', [BotApiController::class, 'createPayment'])->name('api.bot.payment.create');
        Route::get('payment/status', [BotApiController::class, 'paymentStatus'])->name('api.bot.payment.status');
    });
});
