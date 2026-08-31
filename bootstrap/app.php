<?php

use App\Http\Middleware\PublicInvoiceRateLimit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (app()->environment('local')) {
                Route::middleware('web')
                    ->group(base_path('routes/web-email-preview.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'public_invoice_rate_limit' => PublicInvoiceRateLimit::class,
        ]);

        // Configure API middleware
        $middleware->api(prepend: [
            HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
