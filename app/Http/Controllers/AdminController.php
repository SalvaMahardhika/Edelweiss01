<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ================= LIST USERS =================
    public function index()
    {
        // Tetap menyembunyikan Super Admin utama (ID 1) dari daftar
        $users = User::where('id', '!=', 1)->latest()->get();

        // Diarahkan ke view manajemen akun kita
        return view('admin.users.index', compact('users'));
    }

    // ================= CREATE =================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:customer,admin,super_admin',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => 1, // Aktif default
        ]);

        return back()->with('success', 'Pengguna berhasil ditambahkan');
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 🔒 Proteksi super admin utama
        if ($user->id == 1) {
            return back()->with('error', 'Super admin utama tidak bisa diubah');
        }

        // ================= TOGGLE STATUS (LOGIKA AMAN ANDA) =================
        $onlyFields = array_keys($request->except('_token', '_method'));

        if (count($onlyFields) === 1 && in_array('status', $onlyFields)) {
            $user->update([
                'status' => (int) $request->status,
            ]);

            return back()->with('success', 'Status berhasil diubah');
        }

        // ================= UPDATE DATA =================
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            'password' => 'nullable|min:6',
            'role' => 'required|in:customer,admin,super_admin',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->phone = $request->phone;

        // Update password kalau diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Data pengguna berhasil diupdate');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // 🔒 Super admin tidak bisa dihapus
        if ($user->role === 'super_admin' || $user->id == 1) {
            return back()->with('error', 'Super admin tidak bisa dihapus');
        }

        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus');
    }
}
