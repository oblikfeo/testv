<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('v', function (string $expression): string {
            return "<?php echo asset($expression) . '?v=' . (@filemtime(public_path($expression)) ?: time()); ?>";
        });
    }
}
