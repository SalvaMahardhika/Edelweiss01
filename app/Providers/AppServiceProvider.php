<?php

namespace App\Providers;

use App\Helpers\CryptoHelper;
use App\Models\Produk;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🆕 JALAN KELUAR SSL: Bypass SSL cURL secara global khusus di lingkungan local (development)
        if (config('app.env') === 'local') {
            $cacertPath = "C:\xampp\php\extras\ssl\cacert.pem";
            if (file_exists($cacertPath)) {
                ini_set('curl.cainfo', $cacertPath);
                ini_set('openssl.cafile', $cacertPath);
            }
        }

        Route::bind('produk', function ($value) {
            // 1. Dekripsi string acak dari URL untuk mendapatkan ID asli
            $realId = CryptoHelper::decryptId($value);

            // 2. Cari ke database berdasarkan id asli (Tukar 'id_produk' menjadi 'id')
            // Pengecekan status (aktif/nonaktif) diserahkan ke MenuController agar admin tetap bisa lewat
            return Produk::where('id', $realId)->firstOrFail();
        });
    }
}
