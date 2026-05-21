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
        // Admin & super admin bisa melihat semua menu (termasuk yang OFF)
        if (auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            $produk = Produk::latest()->get();
        } else {
            // Pengunjung biasa hanya melihat menu yang aktif
            $produk = Produk::aktif()->latest()->get();
        }

        return view('menu', compact('produk'));
    }

    public function dashboard()
    {
        $produk = Produk::aktif()->latest()->take(3)->get();
        return view('dashboard.dashboard', compact('produk'));
    }

    public function show($id)
    {
        // 1. Cari produk berdasarkan ID tanpa filter 'aktif' terlebih dahulu
        $produk = Produk::where('id_produk', $id)->firstOrFail();

        // 2. Jika produk statusnya NONAKTIF (false / 0)
        if (!$produk->status) {
            
            // Cek apakah user yang login adalah admin atau super_admin
            $isAdmin = auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin']);
            
            // Jika BUKAN admin, kunci aksesnya (bisa pakai abort 404 atau 403)
            if (!$isAdmin) {
                abort(404); // Menggunakan 404 agar pengunjung mengira halaman memang tidak ada
            }
        }

        // 3. Jika lolos pengecekan (produk aktif ATAU pembukanya adalah admin)
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

        // 🔥 PROTEKSI FAILSAFE: Jika form mengirim request toggle_status, 
        // langsung alihkan ke method toggleStatus() agar terhindar dari error validasi di bawah.
        if ($request->has('toggle_status')) {
            return $this->toggleStatus($id);
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

    // ================= TOGGLE STATUS =================
    public function toggleStatus($id)
    {
        if (!in_array(auth()->user()->role, ['admin','super_admin'])) {
            abort(403);
        }

        $produk = Produk::findOrFail($id);

        // Mengubah status (0 jadi 1, atau 1 jadi 0)
        $produk->status = !$produk->status;
        $produk->save();

        return back()->with(
            'success',
            $produk->status
                ? 'Menu berhasil ditampilkan'
                : 'Menu berhasil disembunyikan'
        );
    }

    // ================= DESTROY =================
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