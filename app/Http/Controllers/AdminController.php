<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ================= DASHBOARD UTAMA CMS =================
    public function dashboard()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Total Omzet Bulan Ini
        $omzetBulanIni = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum('amount_paid');

        // 2. Order Masuk Hari Ini
        $orderHariIniCount = Order::whereDate('created_at', $today)->count();

        // 3. Jadwal PO Hari Ini (Berdasarkan tanggal fulfill_at)
        $targetPOHariIniCount = Order::whereDate('fulfill_at', $today)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        // 4. Tagihan DP Belum Lunas
        $pendingDPCount = Order::where('payment_plan', 'dp')
            ->whereIn('payment_status', ['partial', 'unpaid'])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        // 5. Pesanan Terbaru Membutuhkan Konfirmasi (5 order terbaru)
        $recentOrders = Order::latest()
            ->take(5)
            ->get();

        // 6. Target Pembuatan Kue Hari Ini (Agregasi item dari order hari ini)
        $bakingItemsHariIni = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', function ($query) use ($today) {
                $query->whereDate('fulfill_at', $today)
                    ->whereNotIn('status', ['cancelled']);
            })
            ->groupBy('product_name')
            ->get();

        return view('admin.index', compact(
            'omzetBulanIni',
            'orderHariIniCount',
            'targetPOHariIniCount',
            'pendingDPCount',
            'recentOrders',
            'bakingItemsHariIni'
        ));
    }

    // ================= LIST USERS =================
    public function index()
    {
        // Tetap menyembunyikan Super Admin utama (ID 1) dari daftar
        $users = User::where('id', '!=', 1)->latest()->get();

        // Diarahkan ke view manajemen akun
        return view('admin.users.index', compact('users'));
    }

    // ================= CREATE USER =================
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

    // ================= UPDATE USER =================
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 🔒 Proteksi super admin utama
        if ($user->id == 1) {
            return back()->with('error', 'Super admin utama tidak bisa diubah');
        }

        // ================= TOGGLE STATUS =================
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

    // ================= DELETE USER =================
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
