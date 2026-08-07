<?php

use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\RoleMiddleware;
use App\Services\TelegramService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // âœ… REGISTER MIDDLEWARE CUSTOM
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // ðŸ”‘ MASUKKAN CHECK STATUS KE DALAM MIDDLEWARE WEB BERSAMA AUTH
        $middleware->web(append: [
            CheckUserStatus::class,
        ]);

        // ðŸ”‘ PERBAIKAN: Kecualikan rute webhook Midtrans & DOKU dari pemeriksaan token CSRF
        $middleware->validateCsrfTokens(except: [
            'api/midtrans/webhook',
            'api/doku/webhook',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ðŸš¨ KIRIM NOTIFIKASI ERROR LOG OTOMATIS KE TELEGRAM
        $exceptions->reportable(function (Throwable $e) {
            try {
                $telegram = new TelegramService;
                $telegram->sendErrorLog($e);
            } catch (Throwable $telegramEx) {
                // Mencegah error loop jika Telegram API gagal merespons
            }
        });
    })
    ->create();
