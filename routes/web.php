<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DisabledDateController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\LaporanController;
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
// PUBLIC ROUTES & GUEST CHECKOUT (Bisa Diakses Siapa Saja)
// =========================================================================
Route::controller(MenuController::class)->group(function () {
    Route::get('/', 'dashboard')->name('home');
    Route::get('/menu', 'index')->name('menu');
    Route::get('/menu/{produk}', 'show')->name('menu.show');
});

Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri');
Route::view('/about', 'about')->name('about');
Route::view('/kontak', 'kontak')->name('kontak');

// 🔑 RUTE CHECKOUT, PEMBAYARAN, & TRACKING (Support Guest & Authenticated Users)
Route::controller(CheckoutController::class)->group(function () {
    Route::get('/checkout', 'index')->name('checkout.index');
    Route::post('/checkout', 'store')->name('checkout.store');
    Route::get('/checkout/pay/{order_number}', 'pay')->name('checkout.pay');

    // 🆕 RUTE REDIRECT SUKSES PEMBAYARAN DOKU
    Route::get('/checkout/success/{order?}', 'success')->name('checkout.success');

    Route::get('/pesanan/{order_number?}', 'track')->name('orders.track');
});

// 📸 🆕 RUTE UPLOAD BUKTI TRANSFER DARI FRONTEND (Support Guest / Public)
Route::post('/pesanan/upload-proof', [OrderController::class, 'uploadProof'])->name('orders.uploadProof');

// 🔔 WEBHOOK NOTIFIKASI PEMBAYARAN (DOKU & MIDTRANS)
Route::post('/api/midtrans/webhook', [PaymentNotificationController::class, 'handle'])->name('midtrans.webhook');
Route::post('/api/doku/webhook', [PaymentNotificationController::class, 'handleDoku'])->name('doku.webhook');

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
});

// =========================================================================
// CMS ADMIN & SUPER ADMIN ROUTES
// =========================================================================
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // 🟢 HAK AKSES GABUNGAN: ADMIN & SUPER_ADMIN
    Route::middleware(['role:admin,super_admin'])->group(function () {

        // Halaman Utama CMS Dashboard
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.index');

        // 🔒 🆕 MANAJEMEN LOCK TANGGAL / KUOTA LIBUR TOKO
        Route::controller(DisabledDateController::class)->prefix('disabled-dates')->group(function () {
            Route::get('/', 'index')->name('admin.disabled_dates.index');
            Route::post('/', 'store')->name('admin.disabled_dates.store');
            Route::delete('/{id}', 'destroy')->name('admin.disabled_dates.destroy');
        });

        // 🛍️ MANAJEMEN INDUK PESANAN (ORDERS, HISTORY, VERIFIKASI / ORDER MANUAL)
        Route::controller(OrderController::class)->prefix('orders')->group(function () {
            Route::post('/', 'store')->name('orders.store');

            // 📜 Sub-Menu: History Pesanan
            Route::get('/history', 'history')->name('admin.orders.history');

            // 💬 Sub-Menu: Order Manual (Transfer WhatsApp / Manual Direct)
            Route::get('/manual', 'manualOrders')->name('admin.orders.manual');
            Route::patch('/{id}/verify', 'verifyPayment')->name('admin.orders.verifyPayment');
        });

        // 📅 MANAJEMEN JADWAL PO / ANTREAN PRODUKSI
        Route::controller(OrderController::class)->prefix('jadwal-po')->group(function () {
            Route::get('/', 'index')->name('admin.po.index');
            Route::get('/{id}', 'show')->name('admin.po.show');
            Route::patch('/{id}/update-status', 'updateStatus')->name('admin.po.updateStatus');
            Route::patch('/{id}/update-payment', 'updatePaymentStatus')->name('admin.po.updatePayment');
        });

        // 📊 MANAJEMEN LAPORAN PENJUALAN & OMZET
        Route::controller(LaporanController::class)->prefix('laporan')->group(function () {
            Route::get('/', 'index')->name('admin.laporan.index');
            Route::get('/export-excel', 'exportExcel')->name('admin.laporan.exportExcel');
            Route::get('/export-pdf', 'exportPdf')->name('admin.laporan.exportPdf');
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

    // 🛑 HAK AKSES EKSKLUSIF: SUPER_ADMIN ONLY (Manajemen Akun / Pengguna)
    Route::middleware(['role:super_admin'])->controller(AdminController::class)->prefix('users')->group(function () {
        Route::get('/', 'index')->name('admin.users');
        Route::post('/', 'store')->name('admin.store');
        Route::put('/{id}', 'update')->name('admin.update');
        Route::delete('/{id}', 'destroy')->name('admin.destroy');

        // 🆕 Rute Khusus Sakelar/Toggle Status Aktif/Nonaktif Akun
        Route::patch('/{id}/toggle-status', 'toggleStatus')->name('admin.toggleStatus');
    });
});
