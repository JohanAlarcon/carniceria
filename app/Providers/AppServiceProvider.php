<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Tras el mapeo de puertos de Docker (8090->80 interno) Laravel ve el
        // puerto 80 y genera URLs absolutas sin ":8090", desviando redirects y
        // Ziggy al puerto equivocado. En producción forzamos la raíz desde APP_URL.
        if ($this->app->environment('production') && ($appUrl = config('app.url'))) {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
