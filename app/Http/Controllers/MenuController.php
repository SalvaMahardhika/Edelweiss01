<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class MenuController extends Controller
{
    // ================= PUBLIC =================
    public function index()
    {
        $produk = Produk::aktif()->latest()->get();
        return view('menu', compact('produk'));
    }

    public function dashboard()
    {
        $produk = Produk::aktif()->latest()->take(3)->get();
        return view('dashboard.dashboard', compact('produk'));
    }

    public function show($id)
    {
        $produk = Produk::where('id_produk', $id)
            ->aktif()
            ->firstOrFail();

        return view('menu.show', compact('produk'));
    }

    // ================= ADMIN CRUD =================

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin','super_admin'])) {
            abort(403);
        }

        $request->validate([
            'nama_produk' => 'required',
            'harga' => 'required|numeric',
            'deskripsi' => 'required',
            'gambar.*' => 'image'
        ]);

        // 1. simpan produk dulu
        $produk = Produk::create([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'status' => 1,
            'id_user' => Auth::id()
        ]);

        // 2. buat folder
        $folderName = 'menu_' . $produk->id_produk;
        $folderPath = public_path('img/menu/' . $folderName);

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        // 3. simpan gambar
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $i => $file) {
                $filename = ($i + 1) . '.' . $file->getClientOriginalExtension();
                $file->move($folderPath, $filename);
            }
        }

        // 4. simpan nama folder ke DB
        $produk->gambar = $folderName;
        $produk->save();

        return back()->with('success', 'Produk berhasil ditambahkan');
    }


    public function update(Request $request, $id)
{
    if (!in_array(auth()->user()->role, ['admin','super_admin'])) {
        abort(403);
    }

    $produk = Produk::findOrFail($id);

    $folderName = 'menu_' . $produk->id_produk;
    $folderPath = public_path('img/menu/' . $folderName);

    // ================= DELETE IMAGE =================
    if ($request->has('delete_image')) {
        $filePath = $folderPath . '/' . $request->delete_image;

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        return back()->with('success', 'Gambar berhasil dihapus');
    }

    // ================= EDIT FIELD =================
    if ($request->has('field')) {
        $field = $request->field;

        if (in_array($field, ['nama_produk','harga','deskripsi'])) {
            $produk->$field = $request->value;
            $produk->save();

            return back()->with('success', 'Data berhasil diupdate');
        }
    }

    // ================= TAMBAH GAMBAR =================
    if ($request->hasFile('gambar')) {

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $existingFiles = File::files($folderPath);
        $count = count($existingFiles);

        foreach ($request->file('gambar') as $i => $file) {
            $filename = ($count + $i + 1) . '.' . $file->getClientOriginalExtension();
            $file->move($folderPath, $filename);
        }

        return back()->with('success', 'Gambar berhasil ditambahkan');
    }

    // ================= UPDATE FULL DATA =================
    $request->validate([
        'nama_produk' => 'required',
        'harga' => 'required|numeric',
        'deskripsi' => 'required',
    ]);

    $produk->update([
        'nama_produk' => $request->nama_produk,
        'harga' => $request->harga,
        'deskripsi' => $request->deskripsi,
    ]);

    return back()->with('success', 'Produk berhasil diupdate');
}


    public function destroy($id)
    {
        if (!in_array(auth()->user()->role, ['admin','super_admin'])) {
            abort(403);
        }

        $produk = Produk::findOrFail($id);

        $folderPath = public_path('img/menu/' . $produk->gambar);

        // hapus folder
        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $produk->delete();

        return back()->with('success', 'Produk berhasil dihapus');
    }
}