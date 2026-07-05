<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [__DIR__.'/../routes/web.php', __DIR__.'/../routes/admin.php', __DIR__.'/../routes/internal-admin-api.php'],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->validateCsrfTokens(except: ['api/internal/admin/v1/*', 'internal/validation/v1/leads']);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
