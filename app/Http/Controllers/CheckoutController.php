<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request, OrderService $orders, CartService $cart): RedirectResponse
    {
        $items = $cart->items();
        if ($items->isEmpty()) {
            return back()->withErrors('Keranjang kosong.');
        }

        $order = $orders->createOrder($request->validated(), $items->all());
        $cart->clear();

        return redirect()->route('checkout.pay', $order->order_number);
    }

    /**
     * Tampilkan halaman pembayaran Midtrans Snap (Awal / Pembuatan Pesanan).
     */
    public function pay($order_number, MidtransService $midtransService)
    {
        // 1. Cari data order berdasarkan order_number secara aman di server
        $order = Order::where('order_number', $order_number)->firstOrFail();

        // 2. Minta token transaksi ke server Midtrans Sandbox (Otoritas Backend)
        $snapToken = $midtransService->getSnapToken($order, 'initial');

        // 3. Lempar data ke halaman blade view pembayaran awal
        return view('checkout.pay', compact('order', 'snapToken'));
    }

    /**
     * 🔔 BARU: Halaman Publik Lacak Pesanan & Pelunasan Sisa Tagihan (DP)
     * Rute: /pesanan/{order_number}
     */
    public function track($order_number, MidtransService $midtransService)
    {
        // 1. Ambil data order beserta relasi items-nya jika ingin menampilkan detail produk
        $order = Order::with('items')->where('order_number', $order_number)->firstOrFail();

        // 2. Hitung sisa tagihan secara matematis
        $remainingAmount = $order->total_amount - $order->amount_paid;

        $snapToken = null;
        $statusStr = strtolower($order->status instanceof \BackedEnum ? $order->status->value : $order->status);

        // 3. Jika status pesanan masih "partial" atau "pending" dan sisa tagihan > 0, generate token pelunasan
        if (in_array($statusStr, ['partial', 'pending']) && $remainingAmount > 0) {

            // Buat logic parameter khusus pelunasan sisa tagihan
            $snapToken = $midtransService->getSnapToken($order, 'repayment');

            // 💡 Catatan Kreatif: Jika di dalam MidtransService-mu belum dipetakan parameter tipe 'repayment',
            // pastikan di dalam method getSnapToken() milik MidtransService ditambahkan logic kondisional gross_amount
            // untuk mengambil sisa tagihan ini ($order->total_amount - $order->amount_paid).
        }

        // 4. Kirim data ke file view track.blade.php
        return view('orders.track', compact('order', 'remainingAmount', 'snapToken'));
    }
}
