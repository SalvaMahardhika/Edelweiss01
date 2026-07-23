<?php

use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\RoleMiddleware;
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

        // 🔒 PERBAIKAN 1: Percayai Proxy Ngrok agar URL HTTPS terdeteksi sempurna
        $middleware->trustProxies(at: '*');

        // ✅ REGISTER MIDDLEWARE CUSTOM
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // 🔑 MASUKKAN CHECK STATUS KE DALAM MIDDLEWARE WEB
        $middleware->web(append: [
            CheckUserStatus::class,
        ]);

        // 🔑 PERBAIKAN 2: Kecualikan rute Webhook (Midtrans & Scalev) dari pemeriksaan CSRF
        $middleware->validateCsrfTokens(except: [
            'api/midtrans/webhook',
            'api/webhooks/scalev',
            'api/*',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tempat konfigurasi exception penanganan error global jika diperlukan
    })
    ->create();