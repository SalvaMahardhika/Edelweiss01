<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class GaleriController extends Controller
{
    // ================= INDEX =================
    public function index()
    {
        $galeri = Galeri::latest()->get();

        return view('galeri', compact('galeri'));
    }

    // ================= CREATE ALBUM =================
    public function store(Request $request)
    {
        // 🔒 ROLE
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $request->validate([
            'judul' => 'required',
            'deskripsi' => 'required',
            'gambar.*' => 'required|image'
        ]);

        // ================= BUAT DATA DULU =================
        $galeri = Galeri::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'id_user' => Auth::id(),
        ]);

        // ================= BUAT FOLDER =================
        $folderName = 'img' . $galeri->id_galeri;

        $folderPath = public_path('img/galeri/' . $folderName);

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        // ================= UPLOAD GAMBAR =================
        if ($request->hasFile('gambar')) {

            foreach ($request->file('gambar') as $i => $file) {

                $filename = ($i + 1) . '.' . $file->getClientOriginalExtension();

                $file->move($folderPath, $filename);
            }
        }

        // ================= UPDATE ALBUM =================
        $galeri->album = $folderName;
        $galeri->save();

        return back()->with('success', 'Album berhasil dibuat');
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        // 🔒 ROLE
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $galeri = Galeri::findOrFail($id);

        // ================= UPDATE JUDUL =================
        if ($request->field == 'judul') {

            $request->validate([
                'value' => 'required'
            ]);

            $galeri->judul = $request->value;
        }

        // ================= UPDATE DESKRIPSI =================
        if ($request->field == 'deskripsi') {

            $request->validate([
                'value' => 'required'
            ]);

            $galeri->deskripsi = $request->value;
        }

        // ================= TAMBAH FOTO =================
        if ($request->hasFile('gambar')) {

            $folderPath = public_path('img/galeri/' . $galeri->album);

            // hitung file lama
            $existingFiles = collect(File::files($folderPath));

            $start = $existingFiles->count() + 1;

            foreach ($request->file('gambar') as $i => $file) {

                $filename = ($start + $i) . '.' . $file->getClientOriginalExtension();

                $file->move($folderPath, $filename);
            }
        }

        // ================= HAPUS FOTO =================
        if ($request->delete_image) {

            $imagePath = public_path(
                'img/galeri/' .
                $galeri->album . '/' .
                $request->delete_image
            );

            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $galeri->save();

        return back()->with('success', 'Galeri berhasil diupdate');
    }

    // ================= DELETE ALBUM =================
    public function destroy($id)
    {
        // 🔒 ROLE
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $galeri = Galeri::findOrFail($id);

        // ================= HAPUS FOLDER =================
        $folderPath = public_path('img/galeri/' . $galeri->album);

        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        // ================= HAPUS DB =================
        $galeri->delete();

        return back()->with('success', 'Album berhasil dihapus');
    }
}