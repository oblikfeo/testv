<?php

use App\Http\Controllers\Api\BotApiController;
use App\Http\Controllers\Api\BotLinkController;
use App\Http\Controllers\Api\SubscriptionStatusApiController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/sub/{subId}', [SubscriptionController::class, 'show'])->name('api.subscription.show');

Route::middleware(['api.token'])->group(function (): void {
    Route::get('/subscription/status', [SubscriptionStatusApiController::class, 'show'])->name('api.subscription.status');

    Route::prefix('bot')->group(function (): void {
        Route::post('user', [BotApiController::class, 'ensureUser'])->name('api.bot.user');
        Route::get('subscription', [BotApiController::class, 'getSubscription'])->name('api.bot.subscription');
        Route::get('subscriptions', [BotApiController::class, 'listSubscriptions'])->name('api.bot.subscriptions');
        Route::post('trial', [BotApiController::class, 'issueTrial'])->name('api.bot.trial');
        Route::post('trial-feedback', [BotApiController::class, 'submitTrialFeedback'])->name('api.bot.trial-feedback');

        // Привязка существующего web-аккаунта (email) к Telegram через код.
        Route::post('link/start', [BotLinkController::class, 'start'])->name('api.bot.link.start');
        Route::post('link/confirm', [BotLinkController::class, 'confirm'])->name('api.bot.link.confirm');

        // Платежи из бота через YooKassa.
        Route::get('plans', [BotApiController::class, 'listPlans'])->name('api.bot.plans');
        Route::post('payment', [BotApiController::class, 'createPayment'])->name('api.bot.payment.create');
        Route::get('payment/status', [BotApiController::class, 'paymentStatus'])->name('api.bot.payment.status');
    });
});
