<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GaleriController extends Controller
{
    public function index()
    {
        $galeri = Galeri::all();
        return view('galeri', compact('galeri'));
    }

    public function store(Request $request)
    {
        // hitung jumlah album
        $count = Galeri::count() + 1;
        $folderName = 'album' . $count;

        // buat folder
        $path = public_path('img/galeri/' . $folderName);
        File::makeDirectory($path, 0755, true, true);

        // simpan 5 gambar
        for ($i = 1; $i <= 5; $i++) {
            if ($request->hasFile('image'.$i)) {
                $request->file('image'.$i)->move($path, 'image'.$i.'.jpg');
            }
        }

        // simpan DB
        Galeri::create([
            'judul' => $request->judul,
            'album' => $folderName,
            'deskripsi' => $request->deskripsi,
        ]);

        return back();
    }

    public function destroy($id)
    {
        $galeri = Galeri::findOrFail($id);

        // hapus folder
        File::deleteDirectory(public_path('img/galeri/' . $galeri->album));

        $galeri->delete();

        return back();
    }
}