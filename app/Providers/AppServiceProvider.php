<?php

namespace App\Providers;

use App\Contracts\PanelClient;
use App\Services\Panel\ThreeXUiClient;
use Illuminate\Support\Facades\Blade;
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
        // @v('css/landing.css') — versioned asset URL для cache busting:
        // public-файл подмешивает ?v=mtime, чтобы CDN/браузер брали свежую копию
        // после деплоя.
        Blade::directive('v', function (string $expression): string {
            return "<?php echo asset($expression) . '?v=' . (@filemtime(public_path($expression)) ?: time()); ?>";
        });
    }
}
