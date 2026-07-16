<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman edit profil
     */
    public function index()
    {
        return view('profile');
    }

    /**
     * Memproses pembaruan data profil dan password
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        // 1. Validasi Input Data Utama & Kontak WA
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
        ]);

        // 2. Update Data Dasar User
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        // 3. Logika Perubahan Kata Sandi (Hanya diproses jika password_lama DAN password_baru diisi)
        if ($request->password_lama && $request->password_baru) {

            // Cek kecocokan password lama dengan database
            if (! Hash::check($request->password_lama, $user->password)) {
                return back()->with('error', 'Kata sandi lama yang Anda masukkan salah.')->withInput();
            }

            // Validasi kelayakan password baru menggunakan aturan custom name 'password_baru'
            $request->validate([
                'password_baru' => ['required', 'min:8'],
            ], [
                'password_baru.min' => 'Kata sandi baru minimal harus terdiri dari 8 karakter.',
            ]);

            // Cek konfirmasi password secara manual karena nama field custom di form kamu (bukan format _confirmation)
            if ($request->password_baru !== $request->konfirmasi_password) {
                return back()->with('error', 'Konfirmasi kata sandi baru tidak cocok.')->withInput();
            }

            // Enkripsi kata sandi baru
            $user->password = Hash::make($request->password_baru);
        }

        // 4. Simpan perubahan ke database
        $user->save();

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
