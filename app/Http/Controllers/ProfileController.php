<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nama' => 'required',
            'email' => 'required|email',
        ]);

        // update basic
        $user->nama = $request->nama;
        $user->email = $request->email;

        // kalau isi password
        if ($request->password_lama) {

            // cek password lama
            if (!Hash::check($request->password_lama, $user->password)) {
                return back()->with('error', 'Password lama salah');
            }

            // validasi password baru
            if ($request->password_baru !== $request->konfirmasi_password) {
                return back()->with('error', 'Konfirmasi password tidak cocok');
            }

            $user->password = Hash::make($request->password_baru);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui');
    }
}