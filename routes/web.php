<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
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
});


// ==========================================
// SUPER ADMIN ROUTES
// ==========================================
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::controller(AdminController::class)->group(function () {
        Route::get('/admin', 'index')->name('admin.index');
        Route::post('/admin', 'store')->name('admin.store');
        Route::put('/admin/{id}', 'update')->name('admin.update');
        Route::delete('/admin/{id}', 'destroy')->name('admin.destroy');
    });
});