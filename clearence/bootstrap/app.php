<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// basePath stays at the project root so composer.json, vendor/, public/, storage/,
// and bootstrap/ are all found correctly. Individual subdirectories are redirected
// into backend/ where all PHP source lives.
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../backend/routes/web.php',
        api:      __DIR__.'/../backend/routes/api.php',
        commands: __DIR__.'/../backend/routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocaleMiddleware::class);
        $middleware->alias([
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'idempotency' => \App\Http\Middleware\IdempotencyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Redirect PHP source directories into backend/.
$app->useAppPath(dirname(__DIR__).'/backend/app');
$app->useConfigPath(dirname(__DIR__).'/backend/config');
$app->useDatabasePath(dirname(__DIR__).'/backend/database');
$app->useLangPath(dirname(__DIR__).'/backend/lang');

// Infrastructure stays at the project root.
$app->usePublicPath(dirname(__DIR__).'/public');
$app->useStoragePath(dirname(__DIR__).'/storage');
$app->useBootstrapPath(dirname(__DIR__).'/bootstrap');
$app->useEnvironmentPath(dirname(__DIR__));

return $app;
