<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OfflineOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * 📝 Menampilkan Form Input Pesanan Offline / Rekap Lampau
     */
    public function create()
    {
        $products = Produk::select('id', 'nama_produk', 'harga', 'gambar')
            ->orderBy('nama_produk', 'asc')
            ->get();

        return view('admin.orders.offline_create', compact('products'));
    }

    /**
     * 💾 Menyimpan Transaksi Offline Langsung ke Status Completed & Paid
     */
    public function store(Request $request)
    {
        // Mengambil nama tabel riil dari model Produk secara otomatis
        $produkTableName = (new Produk)->getTable();

        $validatedData = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_email'   => 'nullable|email|max:255',
            'order_type'       => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:order_type,delivery|nullable|string',
            'payment_plan'     => 'required|in:full,dp',
            'placed_at'        => 'required|date',
            'fulfill_at'       => 'required|date',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => "required|exists:{$produkTableName},id",
            'items.*.price'      => 'required|numeric|min:0',
            'items.*.quantity'   => 'required|integer|min:1',
        ], [
            'customer_name.required'       => 'Nama pemesan wajib diisi.',
            'customer_phone.required'      => 'Nomor HP/WA wajib diisi.',
            'delivery_address.required_if' => 'Alamat pengiriman wajib diisi jika memilih layanan Delivery.',
            'placed_at.required'           => 'Tanggal pembelian wajib diisi dengan format yang benar.',
            'fulfill_at.required'          => 'Tanggal pengambilan wajib diisi dengan format yang benar.',
            'items.required'               => 'Minimal tambahkan 1 item produk.',
            'items.*.product_id.exists'    => 'Produk yang dipilih tidak ditemukan di database.',
            'items.*.price.numeric'        => 'Harga produk harus berupa angka.',
            'items.*.quantity.min'         => 'Jumlah item minimal 1.',
        ]);

        try {
            // Ambil ID admin yang sedang login (Fallback ke ID 1 jika session terlepas/null)
            $adminUserId = auth()->id() ?? 1;

            $order = $this->orderService->createOfflineOrder($validatedData, $adminUserId);

            // 🟢 TETAP DI HALAMAN OFFLINE REKAP DENGAN FLASH SUCCESS
            return redirect()->route('admin.orders.offline_create')
                ->with('success', 'Berhasil merekap pesanan offline #'.$order->order_number.' ke sistem.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan pesanan offline: '.$e->getMessage());
        }
    }
}