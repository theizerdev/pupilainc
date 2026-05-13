<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\ApplyTemplateLayout::class,
            \App\Http\Middleware\RegionalConfiguration::class,

        ]);
        $middleware->alias([
            'track-active-session' => \App\Http\Middleware\TrackActiveSession::class,
            'superadmin' => App\Http\Middleware\RedirectIfSuperAdmin::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'checkAdminPermission' => \App\Http\Middleware\CheckAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar error 419 (CSRF Token Mismatch) - redirigir automáticamente al login
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            // Invalidar sesión y hacer logout
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Si es logout, simplemente redirigir
            if ($request->is('logout')) {
                return redirect('/')->with('info', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
            }

            // Para otras peticiones, redirigir al login
            return redirect('/')->with('error', 'La página ha expirado. Por favor, recarga la página.');
        });
    })->create();
