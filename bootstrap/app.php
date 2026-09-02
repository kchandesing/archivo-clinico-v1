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
    ->withMiddleware(function (Middleware $middleware) {
        // Enlazar el middleware de forma global para que intercepte todas las peticiones
        $middleware->append(\App\Http\Middleware\CheckIfInstalled::class); 
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
