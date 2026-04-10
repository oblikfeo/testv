<?php

namespace App\Providers;

use App\Contracts\PanelClient;
use App\Services\Panel\ThreeXUiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PanelClient::class, ThreeXUiClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
