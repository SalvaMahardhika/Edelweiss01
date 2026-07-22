<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Category;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    // ==========================================
    // PUBLIC METHODS
    // ==========================================

    public function index(Request $request)
    {
        // Mengambil semua kategori aktif untuk filter di view publik
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $produk = Produk::query()
            ->with('category')
            ->where('status', true)
            ->where('is_available', true) // Konsep PO: Cek ketersediaan slot open order
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderByDesc('is_featured')
            ->orderBy('nama_produk')
            ->get(); // Menggunakan get() sesuai integrasi live filter JavaScript halaman menu sebelumnya

        // 🔧 PERBAIKAN 1: Mengarahkan return ke folder 'menu.index' bukan 'menu' biasa
        return view('menu.index', compact('categories', 'produk'));
    }

    public function dashboard()
    {
        $produk = Produk::query()
            ->where('status', true)
            ->where('is_available', true)
            ->where('is_featured', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('dashboard.dashboard', compact('produk'));
    }

    /**
     * Menampilkan detail produk menggunakan Route Model Binding terenkripsi.
     */
    public function show(Produk $produk)
    {
        if (! $produk->status) {
            $isAdmin = auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin']);
            if (! $isAdmin) {
                abort(404);
            }
        }

        return view('menu.show', compact('produk'));
    }

    // ==========================================
    // CMS ADMIN CRUD METHODS (PRE-ORDER ORIENTED)
    // ==========================================

    /**
     * Menampilkan dashboard utama CMS produk admin
     */
    public function adminIndex()
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $produk = Produk::with('category')->latest()->get();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.menu.index', compact('produk', 'categories'));
    }

    /**
     * Membuka halaman edit data spesifik menu
     */
    public function edit($id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $produk = Produk::findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.menu.edit', compact('produk', 'categories'));
    }

    public function store(Request $request)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        // 🔧 PERBAIKAN 2: Mengubah validasi max file upload gambar menjadi 5MB (5120 KB)
        $request->validate([
            'nama_produk' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'required',
            'gambar.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $slug = Str::slug($request->nama_produk);
        $folderName = time().'_'.$slug;
        $folderPath = public_path('img/menu/'.$folderName);

        // Buat folder penampung gambar fisik terlebih dahulu
        if (! File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        // 1. Simpan Produk dengan data relasi Kategori DB
        $produk = Produk::create([
            'category_id' => $request->category_id,
            'user_id' => Auth::id(),
            'nama_produk' => $request->nama_produk,
            'slug' => $slug,
            'gambar' => $folderName, // Menyimpan nama folder unik ke kolom gambar
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'status' => true,
            'is_available' => true, // Slot PO otomatis aktif saat pertama dibuat
            'is_featured' => false,
        ]);

        // 2. Proses Upload Multiple Images menggunakan ImageHelper Auto-Compress WebP
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $i => $file) {
                $fileName = 'image_'.($i + 1).'.webp';
                $destinationPath = $folderPath.'/'.$fileName;

                try {
                    // Kualitas 75 (Rekomendasi Google), Lebar maksimum dipangkas ke 800px untuk menghemat storage
                    ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 75, 800);
                } catch (\Exception $e) {
                    return back()->with('error', 'Gagal memproses gambar GD WebP: '.$e->getMessage());
                }
            }
        }

        return back()->with('success', 'Produk kue Pre-Order baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        // Failsafe rute: Mengalihkan ke toggleStatus jika ada parameter terkait
        if ($request->has('toggle_status')) {
            return $this->toggleStatus($request, $id);
        }

        $produk = Produk::findOrFail($id);
        $folderName = $produk->gambar;
        $folderPath = public_path('img/menu/'.$folderName);

        // 1. Aksi Hapus File Gambar Spesifik
        if ($request->has('delete_image')) {
            $filePath = $folderPath.'/'.$request->delete_image;
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            return back()->with('success', 'Gambar album berhasil dihapus.');
        }

        // 2. Validasi Data Umum (Wajib ada agar tidak error saat update)
        $request->validate([
            'nama_produk' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'required',
            'gambar.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // 3. Update Data Teks (Selalu dijalankan)
        $produk->update([
            'category_id' => $request->category_id,
            'nama_produk' => $request->nama_produk,
            'slug' => Str::slug($request->nama_produk),
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
        ]);

        // 4. Tambah File Gambar Baru ke Album (Jika ada)
        if ($request->hasFile('gambar')) {
            if (! File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $existingFiles = file_exists($folderPath) ? array_values(array_diff(scandir($folderPath), ['.', '..'])) : [];
            $count = count($existingFiles);

            foreach ($request->file('gambar') as $i => $file) {
                $fileName = 'image_'.($count + $i + 1).'.webp';
                $destinationPath = $folderPath.'/'.$fileName;

                try {
                    ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 75, 800);
                } catch (\Exception $e) {
                    return back()->with('error', 'Gagal memproses gambar tambahan: '.$e->getMessage());
                }
            }

            return redirect()->route('produk.index')->with('success', 'Produk dan foto tambahan berhasil diperbarui.');
        }

        return redirect()->route('produk.index')->with('success', 'Data produk berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, $id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $produk = Produk::findOrFail($id);

        // Cek apakah yang ingin diubah adalah 'is_featured' atau 'status'
        if ($request->has('field') && $request->field == 'is_featured') {
            $produk->is_featured = ! $produk->is_featured;
            $produk->save();

            return back()->with('success', $produk->is_featured ? 'Produk dijadikan unggulan.' : 'Produk tidak lagi unggulan.');
        }

        // Default: Toggle status ketersediaan/aktif
        $produk->status = ! $produk->status;
        $produk->save();

        return back()->with('success', 'Status menu berhasil diubah.');
    }

    public function destroy($id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $produk = Produk::findOrFail($id);
        $folderPath = public_path('img/menu/'.$produk->gambar);

        // 🔧 PERBAIKAN 4: Menghapus folder album fisik beserta seluruh aset foto di dalamnya saat baris produk di-delete
        if ($produk->gambar && File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $produk->delete();

        return back()->with('success', 'Produk kue berhasil dihapus permanen beserta album gambarnya.');
    }
}
