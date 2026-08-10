<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class GaleriController extends Controller
{
    // ==========================================
    // HELPER METHOD PRIVAT (DYNAMIC HOSTING PATH)
    // ==========================================

    /**
     * Menentukan path folder fisik tempat menyimpan album galeri.
     * Mendukung alur Live cPanel (public_html) maupun Localhost (public_path).
     */
    private function getFolderPath(string $folderName): string
    {
        $publicHtmlPath = base_path('../public_html');

        if (file_exists($publicHtmlPath)) {
            return $publicHtmlPath.'/img/galeri/'.$folderName;
        }

        return public_path('img/galeri/'.$folderName);
    }

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
     * Menampilkan daftar utama data galeri di CMS Admin (AJAX Server-Side & Standard Render)
     */
    public function adminIndex(Request $request)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $galeriQuery = Galeri::query()->latest();

            return DataTables::of($galeriQuery)
                ->addIndexColumn()
                ->addColumn('cover', function ($row) {
                    $folderPath = $this->getFolderPath($row->album);
                    $files = File::exists($folderPath) ? array_values(array_diff(scandir($folderPath), ['.', '..'])) : [];
                    $imgUrl = count($files) > 0 ? asset('img/galeri/'.$row->album.'/'.$files[0]) : asset('img/logo/logo2.png');

                    return '
                        <div class="flex justify-center">
                            <img src="'.$imgUrl.'" class="w-12 h-12 object-cover rounded-xl border border-white bg-gray-100 shadow-sm">
                        </div>
                    ';
                })
                ->editColumn('judul', function ($row) {
                    return '
                        <p class="font-bold text-[#2d1f1b]">'.e($row->judul).'</p>
                        <p class="text-[11px] text-amber-800 font-mono mt-0.5">dir: '.e($row->album).'</p>
                    ';
                })
                ->editColumn('deskripsi', function ($row) {
                    return '<p class="text-xs text-gray-500 line-clamp-2 max-w-sm font-normal">'.e($row->deskripsi).'</p>';
                })
                ->addColumn('total_photos', function ($row) {
                    $folderPath = $this->getFolderPath($row->album);
                    $files = File::exists($folderPath) ? array_values(array_diff(scandir($folderPath), ['.', '..'])) : [];

                    return '<div class="text-center text-[#3e2723] font-bold">'.count($files).' Foto</div>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('galeri.edit', $row->id);
                    $deleteUrl = route('galeri.destroy', $row->id);

                    return '
                        <div class="flex items-center justify-center gap-2">
                            <a href="'.$editUrl.'" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Kelola Isi Album">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <button type="button" onclick="triggerDelete(\''.$deleteUrl.'\')" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Hapus Album">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['cover', 'judul', 'deskripsi', 'total_photos', 'action'])
                ->make(true);
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

        $request->validate([
            'judul' => 'required|max:255',
            'deskripsi' => 'required',
            'gambar.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $folderName = time().'_'.Str::slug($request->judul);
        $folderPath = $this->getFolderPath($folderName);

        if (! File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $galeri = Galeri::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'album' => $folderName,
            'user_id' => Auth::id(),
        ]);

        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $i => $file) {
                $fileName = 'photo_'.($i + 1).'.webp';
                $destinationPath = $folderPath.'/'.$fileName;

                try {
                    ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 80, 1000);
                } catch (\Exception $e) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Gagal memproses WebP: '.$e->getMessage()], 422);
                    }

                    return back()->with('error', 'Gagal memproses WebP: '.$e->getMessage());
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Album portofolio baru berhasil diterbitkan.',
                'data' => $galeri,
            ]);
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
        $folderPath = $this->getFolderPath($folderName);

        // Aksi 1: Hapus File Gambar Spesifik (Satuan via AJAX / Form Terpisah)
        if ($request->has('delete_image')) {
            $filePath = $folderPath.'/'.$request->delete_image;
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Foto berhasil dihapus dari album.']);
            }

            return back()->with('success', 'Foto berhasil dihapus dari album.');
        }

        // Aksi 2: Form Edit Utama (Simpan Teks Judul & Deskripsi + Gambar Tambahan Sekaligus)
        $request->validate([
            'judul' => 'required|max:255',
            'deskripsi' => 'required',
            'gambar.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // 1. Simpan Pembaruan Teks ke Database
        $galeri->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        // 2. Simpan Gambar Tambahan (Jika Ada)
        if ($request->hasFile('gambar')) {
            if (! File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $existingFiles = file_exists($folderPath) ? array_values(array_diff(scandir($folderPath), ['.', '..'])) : [];
            $count = count($existingFiles);

            foreach ($request->file('gambar') as $i => $file) {
                // Menggunakan timestamp acak untuk menghindari duplikasi penamaan berkas
                $fileName = 'photo_'.time().'_'.($count + $i + 1).'.webp';
                $destinationPath = $folderPath.'/'.$fileName;

                try {
                    ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 80, 1000);
                } catch (\Exception $e) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Gagal memproses gambar tambahan: '.$e->getMessage()], 422);
                    }

                    return back()->with('error', 'Gagal memproses gambar tambahan: '.$e->getMessage());
                }
            }
        }

        // Response Sukses Tunggal Setelah Teks & Gambar Selesai Diproses
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Album galeri berhasil diperbarui.']);
        }

        return redirect()->route('galeri.index')->with('success', 'Album galeri berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $galeri = Galeri::findOrFail($id);
        $folderPath = $this->getFolderPath($galeri->album);

        if ($galeri->album && File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $galeri->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Album portofolio beserta seluruh foto di dalamnya berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Album portofolio beserta seluruh foto di dalamnya berhasil dihapus.');
    }
}
