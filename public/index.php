<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Create .env from .env.example if it doesn't exist yet (first-run)
if (!file_exists(__DIR__.'/../.env') && file_exists(__DIR__.'/../.env.example')) {
    copy(__DIR__.'/../.env.example', __DIR__.'/../.env');
    // Generate a fresh APP_KEY so encryption works before artisan runs
    $key = 'base64:' . base64_encode(random_bytes(32));
    $env = file_get_contents(__DIR__.'/../.env');
    $env = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $key, $env);
    file_put_contents(__DIR__.'/../.env', $env);
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
