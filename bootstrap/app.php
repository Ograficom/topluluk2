<?php

use App\Http\Middleware\EnsureDeviceIdCookie;
use App\Http\Middleware\DisableDebugbarOnFrontend;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnsureImageAltText;
use App\Http\Middleware\EnsureInstalled;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Middleware\ValidatePostSize;
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
        $middleware->remove([
            ValidatePostSize::class,
        ]);

        $middleware->web(prepend: [
            EnsureInstalled::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            EnsureDeviceIdCookie::class,
            DisableDebugbarOnFrontend::class,
            AddSecurityHeaders::class,
            EnsureImageAltText::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
