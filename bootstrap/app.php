<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(__DIR__.'/../routes/channels.php', ['middleware' => ['web', 'auth']])
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', \App\Http\Middleware\UpdateLastSeenAt::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\\Illuminate\\Session\\TokenMismatchException $exception, \\Illuminate\\Http\\Request $request) {
            return response()->view('errors.419', [], 419);
        });

        $exceptions->render(function (\\Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface $exception, \\Illuminate\\Http\\Request $request) {
            $status = $exception->getStatusCode();

            if (in_array($status, [404, 502], true)) {
                return response()->view("errors.{$status}", [], $status);
            }

            return null;
        });
    })->create();
