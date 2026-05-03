<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\GaleriController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::controller(MenuController::class)->group(function () {

    // Dashboard (ambil produk)
    Route::get('/', 'dashboard')->name('home');

    // Menu
    Route::get('/menu', 'index')->name('menu');

});

Route::controller(GaleriController::class)->group(function () {

    Route::get('/galeri', 'index')->name('galeri');
    Route::post('/galeri', 'store')->name('galeri.store');
    Route::delete('/galeri/{id}', 'destroy')->name('galeri.destroy');

});

// Static Pages
Route::view('/about', 'about')->name('about');
Route::view('/kontak', 'kontak')->name('kontak');