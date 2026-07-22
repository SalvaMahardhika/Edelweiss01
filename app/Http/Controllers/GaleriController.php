<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GaleriController extends Controller
{
    // ==========================================
    // PUBLIC METHODS (DISPLAY CUSTOMER)
    // ==========================================

    public function index()
    {
        $galeri = Galeri::latest()->get();

        // Mengarah ke resources/views/galeri.blade.php (Halaman Publik Bersih)
        return view('galeri', compact('galeri'));
    }

    // ==========================================
    // CMS ADMIN CRUD METHODS (ALBUM ORIENTED)
    // ==========================================

    /**
     * Menampilkan daftar utama data galeri di CMS Admin
     */
    public function adminIndex()
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $galeri = Galeri::latest()->get();

        return view('admin.galeri.index', compact('galeri'));
    }

    /**
     * Membuka panel edit data deskripsi & kelola gambar dalam album
     */
    public function edit($id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $galeri = Galeri::findOrFail($id);

        return view('admin.galeri.edit', compact('galeri'));
    }

    public function store(Request $request)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        // 🔧 PEMBATASAN: Batas maksimal upload ditingkatkan ke 10MB (10240 KB)
        $request->validate([
            'judul' => 'required|max:255',
            'deskripsi' => 'required',
            'gambar.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // Generate nama folder unik berbasis timestamp dan slug judul
        $folderName = time().'_'.Str::slug($request->judul);
        $folderPath = public_path('img/galeri/'.$folderName);

        if (! File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        // 1. Simpan Rekam Data Awal Portofolio ke Database
        $galeri = Galeri::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'album' => $folderName, // Simpan nama folder unik di field album
            'user_id' => Auth::id(),  // Disesuaikan ke standarisasi user_id
        ]);

        // 2. Konversi & Simpan Foto Multiple via ImageHelper WebP
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $i => $file) {
                $fileName = 'photo_'.($i + 1).'.webp';
                $destinationPath = $folderPath.'/'.$fileName;

                try {
                    // Compress otomatis ke WebP dengan max width 1000px agar layout masonry tetap tajam
                    ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 80, 1000);
                } catch (\Exception $e) {
                    return back()->with('error', 'Gagal memproses WebP: '.$e->getMessage());
                }
            }
        }

        return back()->with('success', 'Album portofolio baru berhasil diterbitkan.');
    }

    public function update(Request $request, $id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $galeri = Galeri::findOrFail($id);
        $folderName = $galeri->album;
        $folderPath = public_path('img/galeri/'.$folderName);

        // Aksi 1: Hapus File Gambar Spesifik
        if ($request->has('delete_image')) {
            $filePath = $folderPath.'/'.$request->delete_image;
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            return back()->with('success', 'Foto berhasil dihapus dari album.');
        }

        // Aksi 2: Tambah File Gambar Baru (Gunakan ImageHelper WebP + Limit 10MB)
        if ($request->hasFile('gambar')) {
            $request->validate([
                'gambar.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            ]);

            if (! File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $existingFiles = file_exists($folderPath) ? array_values(array_diff(scandir($folderPath), ['.', '..'])) : [];
            $count = count($existingFiles);

            foreach ($request->file('gambar') as $i => $file) {
                $fileName = 'photo_'.($count + $i + 1).'.webp';
                $destinationPath = $folderPath.'/'.$fileName;

                try {
                    ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 80, 1000);
                } catch (\Exception $e) {
                    return back()->with('error', 'Gagal memproses gambar tambahan: '.$e->getMessage());
                }
            }

            return back()->with('success', 'Foto baru berhasil ditambahkan ke dalam album.');
        }

        // Aksi 3: Perubahan Data Teks dari Form Edit Penuh
        $request->validate([
            'judul' => 'required|max:255',
            'deskripsi' => 'required',
        ]);

        $galeri->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('galeri.index')->with('success', 'Album galeri berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $galeri = Galeri::findOrFail($id);
        $folderPath = public_path('img/galeri/'.$galeri->album);

        // 💥 Bersihkan folder penyimpanan berkas fisik secara total agar storage hemat
        if ($galeri->album && File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $galeri->delete();

        return back()->with('success', 'Album portofolio beserta seluruh foto di dalamnya berhasil dihapus.');
    }
}
