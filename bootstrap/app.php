<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // App servida detrás de un reverse proxy (Apache/Docker). Confiamos SOLO en
        // proxies de redes privadas/loopback para leer el esquema real (https) de
        // X-Forwarded-Proto, sin exponernos a spoofing desde internet.
        $middleware->trustProxies(
            at: ['127.0.0.1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'],
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // DIAGNÓSTICO TEMPORAL (419): Laravel ignora TokenMismatch por defecto;
        // aquí lo registramos con contexto para encontrar la causa. Quitar luego.
        $exceptions->stopIgnoring(\Illuminate\Session\TokenMismatchException::class);
        $exceptions->report(function (\Illuminate\Session\TokenMismatchException $e): void {
            $sessionToken = null;
            try {
                $sessionToken = session()->token();
            } catch (\Throwable $ex) {
            }
            $requestToken = request()->input('_token') ?: request()->header('X-CSRF-TOKEN');

            \Illuminate\Support\Facades\Log::warning('[DIAG-419] CSRF TokenMismatch', [
                'url' => request()->fullUrl(),
                'referer' => request()->header('referer'),
                'host' => request()->getHost(),
                'has_session_cookie' => request()->hasCookie(config('session.cookie')),
                'session_id' => function_exists('session') ? substr((string) session()->getId(), 0, 10).'…' : null,
                'session_token' => $sessionToken ? substr($sessionToken, 0, 10).'…' : '(vacío)',
                'request_token' => $requestToken ? substr((string) $requestToken, 0, 10).'…' : '(vacío)',
                'tokens_match' => $sessionToken && $requestToken && hash_equals($sessionToken, (string) $requestToken),
                'user_id' => auth()->id(),
            ]);
        });
    })->create();
