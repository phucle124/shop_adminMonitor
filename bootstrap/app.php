<?php

use App\Http\Middleware\AdminAuthMiddleware;
use App\Http\Middleware\EnsureKhachHangAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        //Bỏ cơ chế kiểm tra CSRF do chặn URL bắt đầu bằng vqr/...
        $middleware->validateCsrfTokens(except: [
            'vqr/*',
        ]);

        $middleware->alias([
            'khach.auth' => EnsureKhachHangAuthenticated::class,
            'admin.auth' => AdminAuthMiddleware::class,
        ]);

     
    })
    

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })

    ->create();
    
