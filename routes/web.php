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

# ================= PUBLIC =================
Route::controller(MenuController::class)->group(function () {

    Route::get('/', 'dashboard')->name('home');
    Route::get('/menu', 'index')->name('menu');

});

Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri');

Route::view('/about', 'about')->name('about');
Route::view('/kontak', 'kontak')->name('kontak');


# ================= AUTH =================
Route::middleware('guest')->controller(AuthController::class)->group(function () {

    Route::get('/edelweiss-admin', 'loginForm')->name('login');
    Route::post('/edelweiss-admin', 'login')->name('login.process');

});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


# ================= USER =================
Route::middleware('auth')->group(function () {

    // PROFILE
    Route::controller(ProfileController::class)->group(function () {

        Route::get('/profile', 'index')->name('profile');
        Route::post('/profile', 'update')->name('profile.update');

    });

});


# ================= ADMIN =================
Route::middleware(['auth'])->group(function () {

    // GALERI CRUD (admin & super admin)
    Route::controller(GaleriController::class)->group(function () {

        Route::post('/galeri', 'store')->name('galeri.store');
        Route::delete('/galeri/{id}', 'destroy')->name('galeri.destroy');

    });

});


# ================= SUPER ADMIN =================
Route::middleware(['auth', 'role:super_admin'])->group(function () {

    Route::controller(AdminController::class)->group(function () {

        Route::get('/admin', 'index')->name('admin.index');
        Route::post('/admin', 'store')->name('admin.store');
        Route::put('/admin/{id}', 'update')->name('admin.update');
        Route::delete('/admin/{id}', 'destroy')->name('admin.destroy');

    });

});