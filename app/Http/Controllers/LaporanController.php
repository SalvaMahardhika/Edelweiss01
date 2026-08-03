<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Utama: Hanya order 'completed'
        $query = Order::with(['items', 'payments'])->where('status', 'completed');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), [$startDate, $endDate]);
            });
        }

        if ($request->filled('order_type') && $request->order_type !== 'ALL') {
            $query->where('order_type', $request->order_type);
        }

        // Data Pesanan Selesai
        $completedOrders = (clone $query)->latest('created_at')->paginate(10)->withQueryString();

        // 2. Ringkasan KPI
        $totalOmzet = (clone $query)->sum('total_amount');
        $totalPesanan = (clone $query)->count();
        $avgOrderVal = $totalPesanan > 0 ? ($totalOmzet / $totalPesanan) : 0;

        // Callback Filter Tanggal
        $dateFilterCallback = function ($q) use ($startDate, $endDate, $request) {
            $q->where('status', 'completed');
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $q->whereBetween(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), [$startDate, $endDate]);
            }
            if ($request->filled('order_type') && $request->order_type !== 'ALL') {
                $q->where('order_type', $request->order_type);
            }
        };

        // Total Produk Terjual (Pcs)
        $totalProdukTerjual = OrderItem::whereHas('order', $dateFilterCallback)->sum('quantity');

        // Top 5 Produk Terlaris
        $topProducts = OrderItem::whereHas('order', $dateFilterCallback)
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 3. 📈 DATA GRAFIK PENJUALAN HARIAN
        $chartQuery = (clone $query)
            ->select(
                DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at)) as date'),
                DB::raw('SUM(total_amount) as daily_revenue'),
                DB::raw('COUNT(id) as daily_orders')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $chartLabels = $chartQuery->pluck('date')->map(fn ($d) => date('d M', strtotime($d)));
        $chartData = $chartQuery->pluck('daily_revenue');

        return view('admin.laporan.index', compact(
            'completedOrders',
            'totalOmzet',
            'totalPesanan',
            'totalProdukTerjual',
            'avgOrderVal',
            'topProducts',
            'startDate',
            'endDate',
            'chartLabels',
            'chartData'
        ));
    }

    // 📊 EXPORT KE EXCEL / CSV (Aman dari Enum Type Error)
    public function exportExcel(Request $request)
    {
        // Bersihkan semua output buffer sebelum membuat file CSV
        while (ob_get_level()) {
            ob_end_clean();
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Order::with('items')->where('status', 'completed');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween(DB::raw('DATE(COALESCE(fulfill_at, placed_at, created_at))'), [$startDate, $endDate]);
        }

        if ($request->filled('order_type') && $request->order_type !== 'ALL') {
            $query->where('order_type', $request->order_type);
        }

        $orders = $query->latest('created_at')->get();

        $fileName = 'Laporan_Penjualan_Edelweiss_'.date('Y-m-d_H-i').'.csv';

        // Tulis file CSV ke memori sementara
        $tempFile = fopen('php://temp', 'r+');

        // UTF-8 BOM agar Microsoft Excel membaca format dengan rapi
        fwrite($tempFile, "\xEF\xBB\xBF");

        // Header Kolom Excel
        fputcsv($tempFile, [
            'No. Order',
            'Tanggal Pemesanan',
            'Nama Pelanggan',
            'No. HP',
            'Tipe Pesanan',
            'Item Kue / Roti',
            'Status Pembayaran',
            'Total Nominal (Rp)',
        ]);

        // Baris Data Transaksi
        foreach ($orders as $order) {
            $itemList = $order->items->map(function ($i) {
                return $i->quantity.'x '.str_replace(["\r", "\n", '"'], '', $i->product_name);
            })->implode(' | ');

            $placedDate = $order->fulfill_at ?? $order->placed_at ?? $order->created_at;

            // 🛠️ SAFE ENUM CONVERSION TO STRING
            $orderTypeVal = is_object($order->order_type) ? $order->order_type->value : (string) $order->order_type;
            $paymentStatusVal = is_object($order->payment_status) ? $order->payment_status->value : (string) $order->payment_status;

            fputcsv($tempFile, [
                $order->order_number,
                $placedDate ? date('d/m/Y H:i', strtotime($placedDate)) : '-',
                $order->customer_name,
                ' '.$order->customer_phone,
                strtoupper($orderTypeVal),
                $itemList,
                strtoupper($paymentStatusVal),
                $order->total_amount,
            ]);
        }

        rewind($tempFile);
        $csvContent = stream_get_contents($tempFile);
        fclose($tempFile);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
