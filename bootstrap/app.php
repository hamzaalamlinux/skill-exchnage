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
         $middleware->alias([
            'auth'=> \App\Http\Middleware\Authenticate::class,
            'is_admin' => \App\Http\Middleware\AdminMiddleware::class,
            'auth.api' => \App\Http\Middleware\CheckToken::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(
            function (\Illuminate\Auth\AuthenticationException $e, $request) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        );
    })->create();
