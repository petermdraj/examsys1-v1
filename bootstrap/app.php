<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$instance    = $_SERVER['EXAMSYS_INSTANCE'] ?? null;
$envFile     = $instance ? ".env.{$instance}" : '.env';
$storagePath = $instance
    ? dirname(__DIR__) . "/storage/{$instance}"
    : dirname(__DIR__) . '/storage';

if ($instance) {
    $prefix = '/' . $instance;
    foreach (['REQUEST_URI', 'PHP_SELF', 'PATH_INFO'] as $key) {
        if (isset($_SERVER[$key]) && str_starts_with($_SERVER[$key], $prefix)) {
            $_SERVER[$key] = substr($_SERVER[$key], strlen($prefix)) ?: '/';
        }
    }
}

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\EnsureInstalled::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'otp.verified' => \App\Http\Middleware\RedirectUnverifiedUsers::class,
            'lecturer'     => \App\Http\Middleware\EnsureLecturer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

$app->loadEnvironmentFrom($envFile);
$app->useStoragePath($storagePath);

return $app;
