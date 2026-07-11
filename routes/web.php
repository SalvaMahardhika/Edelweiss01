<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
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

// ==========================================
// PUBLIC ROUTES (Bisa Diakses Siapa Saja & Guest Checkout)
// ==========================================
Route::controller(MenuController::class)->group(function () {
    Route::get('/', 'dashboard')->name('home');
    Route::get('/menu', 'index')->name('menu');
    Route::get('/menu/{produk}', 'show')->name('menu.show'); 
});

Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri');
Route::view('/about', 'about')->name('about');
Route::view('/kontak', 'kontak')->name('kontak');

// 🔑 RUTE PEMBAYARAN & PEMELIHARAAN PUBLIK
Route::get('/pesanan/{order_number}', [CheckoutController::class, 'track'])->name('orders.track');
Route::post('/api/midtrans/webhook', [PaymentNotificationController::class, 'handle'])->name('midtrans.webhook');

// 🔑 SOLUSI GUEST CHECKOUT: Dikeluarkan dari middleware auth agar pelanggan non-login bisa checkout!
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');


// ==========================================
// AUTHENTICATION ROUTES (GUEST ONLY)
// ==========================================
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/edelweiss-admin', 'loginForm')->name('login');
    Route::post('/edelweiss-admin', 'login')->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


// ==========================================
// USER ROUTES (AUTHENTICATED CUSTOMERS ONLY)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::post('/profile', 'update')->name('profile.update');
    });

    Route::get('/checkout/pay/{order_number}', [CheckoutController::class, 'pay'])->name('checkout.pay');
});


// ==========================================
// ADMIN ROUTES (MANAJEMEN & HAK AKSES)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // Galeri Management
    Route::controller(GaleriController::class)->group(function () {
        Route::post('/galeri', 'store')->name('galeri.store');
        Route::put('/galeri/{id}', 'update')->name('galeri.update');
        Route::delete('/galeri/{id}', 'destroy')->name('galeri.destroy');
    });

    // Produk / Menu Management
    Route::prefix('admin/menu')->controller(MenuController::class)->group(function () {
        Route::post('/', 'store')->name('produk.store');
        Route::put('/{id}', 'update')->name('produk.update');
        Route::delete('/{id}', 'destroy')->name('produk.destroy');
        Route::patch('/{id}/toggle-status', 'toggleStatus')->name('produk.toggleStatus');
    });

    // ADMIN AREA – admin & super_admin
    Route::prefix('admin')->middleware(['role:admin,super_admin'])->group(function () {
        Route::get('/', fn () => response('admin home'))->name('admin.index');
    });

    // ADMIN USERS – super_admin only
    Route::prefix('admin/users')->middleware(['role:super_admin'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.users');
        Route::post('/', [AdminController::class, 'store'])->name('admin.store');
        Route::put('/{id}', [AdminController::class, 'update'])->name('admin.update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    });

    // Order Management
    Route::prefix('admin/orders')->controller(OrderController::class)->group(function () {
        Route::post('/', 'store')->name('orders.store');
    });
});