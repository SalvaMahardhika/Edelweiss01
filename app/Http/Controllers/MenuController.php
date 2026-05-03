<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Halaman Menu (semua produk)
     */
    public function index()
    {
        $produk = Produk::where('status', 1)
                    ->latest()
                    ->get();

        return view('menu', compact('produk'));
    }

    /**
     * Dashboard (preview beberapa produk saja)
     */
    public function dashboard()
    {
        $produk = Produk::where('status', 1)
                    ->latest()
                    ->take(3) // tampilkan 3 produk saja
                    ->get();

        return view('dashboard.dashboard', compact('produk'));
    }

    /**
     * (Optional) Detail produk
     */
    public function show($id)
    {
        $produk = Produk::findOrFail($id);

        return view('menu.show', compact('produk'));
    }
}