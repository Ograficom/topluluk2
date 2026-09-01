<?php

use App\Http\Middleware\EnsureDeviceIdCookie;
use App\Http\Middleware\DisableDebugbarOnFrontend;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\CommentSkeletonPreloaderMiddleware;
use App\Http\Middleware\ContactPageFieldStyleMiddleware;
use App\Http\Middleware\EditorJsTableInlineFormatMiddleware;
use App\Http\Middleware\EnsureImageAltText;
use App\Http\Middleware\EnsureInstalled;
use App\Http\Middleware\LoginPageSecurityMiddleware;
use App\Http\Middleware\PostPresentationMiddleware;
use App\Http\Middleware\PostShowCommentIdentityLayoutMiddleware;
use App\Http\Middleware\PostShowReactionLayoutMiddleware;
use App\Http\Middleware\PostShowTypographyMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VideoPageResponseMiddleware;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->remove([
            ValidatePostSize::class,
        ]);

        $middleware->web(prepend: [
            EnsureInstalled::class,
        ]);

        // Keep the post-show-only mobile layout fix isolated from feed/category cards.
        $middleware->web(append: [
            SetLocale::class,
            EnsureDeviceIdCookie::class,
            DisableDebugbarOnFrontend::class,
            AddSecurityHeaders::class,
            EnsureImageAltText::class,
            EditorJsTableInlineFormatMiddleware::class,
            PostShowTypographyMiddleware::class,
            PostPresentationMiddleware::class,
            PostShowReactionLayoutMiddleware::class,
            PostShowCommentIdentityLayoutMiddleware::class,
            CommentSkeletonPreloaderMiddleware::class,
            LoginPageSecurityMiddleware::class,
            ContactPageFieldStyleMiddleware::class,
            VideoPageResponseMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
