<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Halaman Utama Jadwal PO
    public function index(Request $request)
    {
        $query = Order::with(['items', 'payments'])->latest();

        // Filter berdasarkan tanggal Fulfill/Siap
        if ($request->filled('date')) {
            $query->whereDate('fulfill_at', $request->date);
        }

        // Filter Status Order
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        // Filter Status Pembayaran
        if ($request->filled('payment_status') && $request->payment_status !== 'ALL') {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(15);

        // Rekap ringkasan pesanan hari ini
        $todayPO = Order::whereDate('fulfill_at', now())->count();
        $pendingPO = Order::where('status', 'pending')->count();
        $preparingPO = Order::where('status', 'preparing')->count();

        return view('admin.po.index', compact('orders', 'todayPO', 'pendingPO', 'preparingPO'));
    }

    // Update Status Pengerjaan Pesanan
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan '.$order->order_number.' berhasil diperbarui.');
    }

    // Update Status Pelunasan Manual (Misal Pelanggan Bayar Sisa DP secara Tunai saat Pickup)
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid,refunded',
        ]);

        $order = Order::findOrFail($id);
        $order->update([
            'payment_status' => $request->payment_status,
            'amount_paid' => $request->payment_status === 'paid' ? $order->total_amount : $order->amount_paid,
        ]);

        return back()->with('success', 'Status pembayaran '.$order->order_number.' berhasil diperbarui.');
    }
}
