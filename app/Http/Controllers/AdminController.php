<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ================= LIST ADMIN =================
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        return view('admin.index', compact('admins'));
    }

    // ================= CREATE =================
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|min:6'
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'status' => 1
        ]);

        return back()->with('success', 'Admin berhasil ditambahkan');
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 🔒 proteksi super admin utama
        if ($user->id_user == 1) {
            return back()->with('error', 'Super admin utama tidak bisa diubah');
        }

        // ================= TOGGLE STATUS (FIX AMAN) =================
        $onlyFields = array_keys($request->except('_token', '_method'));

        if (count($onlyFields) === 1 && in_array('status', $onlyFields)) {
            $user->update([
                'status' => (int) $request->status
            ]);

            return back()->with('success', 'Status berhasil diubah');
        }

        // ================= UPDATE DATA =================
        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:user,email,' . $id . ',id_user',
            'password' => 'nullable|min:6'
        ]);

        $user->nama = $request->nama;
        $user->email = $request->email;

        // update password kalau diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Admin berhasil diupdate');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // 🔒 super admin tidak bisa dihapus
        if ($user->role === 'super_admin' || $user->id_user == 1) {
            return back()->with('error', 'Super admin tidak bisa dihapus');
        }

        $user->delete();

        return back()->with('success', 'Admin berhasil dihapus');
    }
}