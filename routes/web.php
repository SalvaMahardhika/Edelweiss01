<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController; // 🆕 Import CategoryController
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentNotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Edelweiss Bakery - Dokumen Rute Web (web.php)
|--------------------------------------------------------------------------
*/

// =========================================================================
// PUBLIC ROUTES (Bisa Diakses Siapa Saja & Guest Pre-Order)
// =========================================================================
Route::controller(MenuController::class)->group(function () {
    Route::get('/', 'dashboard')->name('home');
    Route::get('/menu', 'index')->name('menu');
    Route::get('/menu/{produk}', 'show')->name('menu.show');
});

Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri');
Route::view('/about', 'about')->name('about');
Route::view('/kontak', 'kontak')->name('kontak');

// 🔑 RUTE PEMBAYARAN & LAZIM PRE-ORDER PUBLIK
Route::get('/pesanan/{order_number}', [CheckoutController::class, 'track'])->name('orders.track');
Route::post('/api/midtrans/webhook', [PaymentNotificationController::class, 'handle'])->name('midtrans.webhook');

// 🔑 SOLUSI GUEST CHECKOUT
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// =========================================================================
// AUTHENTICATION ROUTES (GUEST ONLY)
// =========================================================================
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/edelweiss-admin', 'loginForm')->name('login');
    Route::post('/edelweiss-admin', 'login')->name('login.process');

    Route::get('/register', 'registerForm')->name('register');
    Route::post('/register', 'register')->name('register.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// =========================================================================
// USER ROUTES (AUTHENTICATED CUSTOMERS ONLY)
// =========================================================================
Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::post('/profile', 'update')->name('profile.update');
    });

    // Tampilan Formulir & Proses Simpan Checkout Pre-Order
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

    Route::get('/checkout/pay/{order_number}', [CheckoutController::class, 'pay'])->name('checkout.pay');
});

// =========================================================================
// CMS ADMIN & SUPER ADMIN ROUTES
// =========================================================================
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // 🟢 HAK AKSES GABUNGAN: ADMIN & SUPER_ADMIN
    Route::middleware(['role:admin,super_admin'])->group(function () {

        // Halaman Utama CMS Dashboard
        Route::get('/', fn () => view('admin.index'))->name('admin.index');

        // Order Management
        Route::controller(OrderController::class)->prefix('orders')->group(function () {
            Route::post('/', 'store')->name('orders.store');
        });

        // 📝 Produk / Menu Management (CRUD Lengkap)
        Route::controller(MenuController::class)->prefix('menu')->group(function () {
            Route::get('/', 'adminIndex')->name('produk.index');
            Route::post('/', 'store')->name('produk.store');
            Route::get('/{id}/edit', 'edit')->name('produk.edit');
            Route::put('/{id}', 'update')->name('produk.update');
            Route::delete('/{id}', 'destroy')->name('produk.destroy');

            // Rute Toggle Status (Menangani is_featured dan status aktif)
            Route::patch('/{id}/toggle-status', 'toggleStatus')->name('produk.toggleStatus');
        });

        // 🏷️ Kategori Management (CRUD Lengkap)
        Route::controller(CategoryController::class)->prefix('kategori')->group(function () {
            Route::get('/', 'index')->name('kategori.index');
            Route::post('/', 'store')->name('kategori.store');
            Route::put('/{id}', 'update')->name('kategori.update');
            Route::patch('/{id}/toggle', 'toggleStatus')->name('kategori.toggle');
            Route::delete('/{id}', 'destroy')->name('kategori.destroy');
        });

        // 🖼️ Galeri Management (CRUD Lengkap)
        Route::controller(GaleriController::class)->prefix('galeri')->group(function () {
            Route::get('/', 'adminIndex')->name('galeri.index');
            Route::post('/', 'store')->name('galeri.store');
            Route::get('/{id}/edit', 'edit')->name('galeri.edit');
            Route::put('/{id}', 'update')->name('galeri.update');
            Route::delete('/{id}', 'destroy')->name('galeri.destroy');
        });
    });

    // 🛑 HAK AKSES EKSKLUSIF: SUPER_ADMIN ONLY
    Route::middleware(['role:super_admin'])->prefix('users')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.users');
        Route::post('/', [AdminController::class, 'store'])->name('admin.store');
        Route::put('/{id}', [AdminController::class, 'update'])->name('admin.update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    });
});
