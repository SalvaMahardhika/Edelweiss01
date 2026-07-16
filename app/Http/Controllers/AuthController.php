<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function loginForm()
    {
        // Pengecekan jika user sudah login agar tidak bisa akses form login lagi
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin' || $user->role === 'super_admin') {
                return redirect()->route('admin.index');
            }

            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Email atau password salah')->withInput($request->only('email'));
        }

        // Pastikan status akun aktif sebelum mengizinkan login
        if (! $user->status) {
            return back()->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi Super Admin.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        // 🔑 REDIRECT BERDASARKAN ROLE
        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return redirect()->route('admin.index'); // Langsung ke http://127.0.0.1:8000/admin
        }

        return redirect()->route('home');
    }

    /**
     * 🆕 Menampilkan halaman form pendaftaran akun customer baru
     */
    public function registerForm()
    {
        // Jika sudah login, tendang balik agar tidak bisa akses halaman register
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin' || $user->role === 'super_admin') {
                return redirect()->route('admin.index');
            }

            return redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * 🆕 Memproses data registrasi customer
     */
    public function register(Request $request)
    {
        // 1. Validasi data inputan
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Email ini sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ]);

        // 2. Buat data user baru dengan default role customer
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer', // Mengunci registrasi publik hanya sebagai customer
            'status' => true,       // Otomatis aktif
        ]);

        // 3. Otomatis login setelah berhasil mendaftar
        Auth::login($user);

        // 4. Regenerasi session demi keamanan
        $request->session()->regenerate();

        // 5. Alihkan ke halaman home / katalog pre-order
        return redirect()->route('home')->with('success', 'Pendaftaran akun berhasil!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Hancurkan session dengan aman saat logout
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
