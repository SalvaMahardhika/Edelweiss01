<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Edelweiss Bakery - Dokumen Rute Web (web.php)
|--------------------------------------------------------------------------
|
| File ini mendefinisikan seluruh rute web untuk aplikasi Edelweiss Bakery.
| Rute-rute di sini dimuat oleh RouteServiceProvider dan menggunakan grup
| middleware "web" serta middleware keamanan/autentikasi tambahan.
|
*/

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::controller(MenuController::class)->group(function () {
    Route::get('/', 'dashboard')->name('home');
    Route::get('/menu', 'index')->name('menu');

    // Menggunakan {produk} untuk mendukung Custom Route Model Binding (AES-256)
    Route::get('/menu/{produk}', 'show')->name('menu.show');
});

Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri');
Route::view('/about', 'about')->name('about');
Route::view('/kontak', 'kontak')->name('kontak');

// ==========================================
// AUTHENTICATION ROUTES
// ==========================================
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/edelweiss-admin', 'loginForm')->name('login');
    Route::post('/edelweiss-admin', 'login')->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ==========================================
// USER ROUTES (AUTHENTICATED)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::post('/profile', 'update')->name('profile.update');
    });
});

// ==========================================
// ADMIN ROUTES
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

    // ADMIN AREA – accessible by admin & super_admin
    Route::prefix('admin')->middleware(['role:admin,super_admin'])->group(function () {
        Route::get('/', fn () => response('admin home'))->name('admin.index');
    });

    // ADMIN USERS – only super_admin
    Route::prefix('admin/users')->middleware(['role:super_admin'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.users');
        // CRUD routes
        Route::post('/', [AdminController::class, 'store'])->name('admin.store');
        Route::put('/{id}', [AdminController::class, 'update'])->name('admin.update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    });
});

// ==========================================
// SUPER ADMIN ROUTES
// ==========================================
// Super‑admin specific admin management routes removed to avoid conflict with custom admin middleware routes.
// You can re‑add them under a different prefix (e.g., /super-admin) if needed.

/*
|--------------------------------------------------------------------------
| Akhir dari File Rute Web (web.php)
|--------------------------------------------------------------------------
|
| Semua rute aplikasi Edelweiss Bakery didefinisikan di atas. Pastikan untuk
| selalu memperhatikan hak akses (middleware) seperti 'auth' dan 'role:super_admin'
| saat menambahkan rute baru demi menjaga keamanan sistem.
|
*/
