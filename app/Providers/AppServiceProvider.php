<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use function Livewire\on;

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

        // DIAGNÓSTICO TEMPORAL (419 en Reportes): registra en el log la causa real
        // que Livewire oculta como "página expirada". Quitar cuando se resuelva.
        if ($this->app->environment('local')) {
            // Caso 1: el "sello" del componente no coincide (serialización).
            on('checksum.fail', function ($checksum = null, $comparitor = null, $snapshot = null): void {
                try {
                    Log::warning('[DIAG-419] checksum.fail', [
                        'component' => data_get($snapshot, 'memo.name'),
                        'url' => request()->fullUrl(),
                        'data' => data_get($snapshot, 'data'),
                    ]);
                } catch (\Throwable $e) {
                }
            });

            // Caso 2: una excepción real (p.ej. TypeError) durante la actualización.
            on('exception', function ($target = null, $e = null, $stopPropagation = null): void {
                try {
                    Log::error('[DIAG-419] exception: '.($e ? get_class($e).': '.$e->getMessage() : 'desconocida'), [
                        'component' => is_object($target) && method_exists($target, 'getName') ? $target->getName() : null,
                        'at' => $e ? $e->getFile().':'.$e->getLine() : null,
                        'url' => request()->fullUrl(),
                    ]);
                } catch (\Throwable $ex) {
                }
            });
        }

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
