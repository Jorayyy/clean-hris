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
    ->withMiddleware(function (Middleware $middleware): void {
        // Automatically redirects unauthenticated guests back to the login page safely
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->alias([
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'prevent_back_history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\PreventBackHistory::class);
        
        // Disabled for localhost development to prevent HTTP/HTTPS cookie redirect loops
        // $middleware->appendToGroup('web', \App\Http\Middleware\ForceSecureCookies::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
