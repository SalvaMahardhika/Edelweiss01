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
        Route::bind('produk', function ($value) {
            // 1. Dekripsi string acak dari URL untuk mendapatkan ID asli
            $realId = CryptoHelper::decryptId($value);

            // 2. Cari ke database berdasarkan id_produk asli
            // Pengecekan status (aktif/nonaktif) diserahkan ke MenuController agar admin tetap bisa lewat
            return Produk::where('id_produk', $realId)->firstOrFail();
        });
    }
}
