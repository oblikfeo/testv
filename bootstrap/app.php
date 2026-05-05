<?php

use App\Models\Pair;
use App\Services\KeyPoolService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'payment/webhook',
        ]);
        $middleware->alias([
            'api.token' => \App\Http\Middleware\VerifyApiToken::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(function (): void {
            foreach (Pair::query()->where('is_active', true)->orderBy('sort_order')->get() as $pair) {
                try {
                    app(KeyPoolService::class)->ensurePoolForPair($pair);
                } catch (\Throwable $e) {
                    Log::warning('schedule.ensurePoolForPair', [
                        'pair_id' => $pair->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        })->everyMinute()->name('ensure-key-pools');

        $schedule->command('subscriptions:check-expired')->hourly();
        $schedule->command('payments:reconcile-refunds --hours=336')->everyTenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
