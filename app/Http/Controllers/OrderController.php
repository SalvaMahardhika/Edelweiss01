<?php

namespace App\Http\Controllers;

use App\Events\OrderUpdated;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentProofService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentProofService $proofService
    ) {}

    /**
     * 📅 Halaman Utama Jadwal PO (KHUSUS PAYMENT GATEWAY)
     */
    public function index(Request $request)
    {
        // ⚡ HANYA jalankan auto-cancel pada request HTTP biasa (Bukan AJAX Polling DataTables)
        if (! $request->ajax()) {
            $this->orderService->autoCancelExpiredOrders();
        }

        if ($request->ajax()) {
            // Eager Loading kolom spesifik untuk efisiensi memori & I/O
            $query = Order::with([
                'items:id,order_id,product_name,quantity',
                'payments:id,order_id,amount,status',
            ])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'manual_wa')
                        ->orWhereNull('payment_method');
                });

            // 1. FILTER TANGGAL
            if ($request->filled('date')) {
                $query->whereDate('fulfill_at', $request->date);
            }
            // 2. FILTER STATUS PRODUKSI
            if ($request->filled('status') && $request->status !== 'ALL') {
                $query->where('status', $request->status);
            }
            // 3. FILTER STATUS PEMBAYARAN
            if ($request->filled('payment_status') && $request->payment_status !== 'ALL') {
                $query->where('payment_status', $request->payment_status);
            }

            // 🔍 4. PENANGANAN FITUR SEARCH GLOBAL (Cari Order Number, Nama, Phone, atau Nama Produk)
            $searchValue = null;
            if ($request->filled('search')) {
                $searchValue = is_array($request->search) ? ($request->search['value'] ?? null) : $request->search;
            }

            if (! empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('order_number', 'like', "%{$searchValue}%")
                        ->orWhere('customer_name', 'like', "%{$searchValue}%")
                        ->orWhere('customer_phone', 'like', "%{$searchValue}%")
                        ->orWhereHas('items', function ($itemQuery) use ($searchValue) {
                            $itemQuery->where('product_name', 'like', "%{$searchValue}%");
                        });
                });
            }

            // ⚡ EFISIENSI TINGGI: Hitung 3 statistik sekaligus dalam 1 Query Tunggal
            $statsData = Order::whereNotIn('status', ['completed', 'cancelled'])
                ->where(fn ($q) => $q->where('payment_method', '!=', 'manual_wa')->orWhereNull('payment_method'))
                ->selectRaw("
                    COUNT(CASE WHEN DATE(fulfill_at) = CURRENT_DATE() THEN 1 END) as today_po,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_po,
                    COUNT(CASE WHEN status = 'preparing' THEN 1 END) as preparing_po
                ")->first();

            $stats = [
                'todayPO' => $statsData->today_po ?? 0,
                'pendingPO' => $statsData->pending_po ?? 0,
                'preparingPO' => $statsData->preparing_po ?? 0,
            ];

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return is_object($row->status) ? ($row->status->value ?? (string) $row->status) : (string) $row->status;
                })
                ->editColumn('payment_status', function ($row) {
                    // 🟢 KEMBALIKAN STRING MURNI (agar JS DataTables di Blade bisa memproses badge & lock status dengan presisi)
                    return is_object($row->payment_status) ? ($row->payment_status->value ?? (string) $row->payment_status) : (string) $row->payment_status;
                })
                ->editColumn('fulfill_at', fn ($row) => $row->fulfill_at ? date('d M Y, H:i', strtotime($row->fulfill_at)).' WIB' : '-')
                ->editColumn('total_amount', fn ($row) => 'Rp '.number_format((float) $row->total_amount, 0, ',', '.'))
                ->addColumn('action', fn ($row) => '
                    <button type="button" onclick="viewOrderDetail('.$row->id.')" class="p-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition text-xs font-bold">
                        <i class="fa-solid fa-eye"></i> Detail
                    </button>
                ')
                ->rawColumns(['action'])
                ->with(['stats' => $stats])
                // 🔄 MEMUNGKINKAN YAJRA DATATABLES MENG-HANDLE SORTING BAWAAN DATATABLES (ORDER BY COLUMN)
                ->order(function ($query) use ($request) {
                    if ($request->has('order') && is_array($request->order) && count($request->order) > 0) {
                        $columnIndex = $request->order[0]['column'] ?? null;
                        $columnDir = $request->order[0]['dir'] ?? 'asc';
                        $columns = $request->columns ?? [];

                        if ($columnIndex !== null && isset($columns[$columnIndex]['name'])) {
                            $columnName = $columns[$columnIndex]['name'];
                            // Mapping kolom nama ke database
                            if (in_array($columnName, ['order_number', 'fulfill_at', 'payment_status', 'status', 'created_at'])) {
                                $query->orderBy($columnName, $columnDir);

                                return;
                            }
                        }
                    }

                    // Fallback jika tidak ada order spesifik dari header tabel
                    if ($request->filled('sort_by')) {
                        switch ($request->sort_by) {
                            case 'fulfill_asc': $query->orderBy('fulfill_at', 'asc');
                                break;
                            case 'fulfill_desc': $query->orderBy('fulfill_at', 'desc');
                                break;
                            case 'created_asc': $query->orderBy('created_at', 'asc');
                                break;
                            case 'created_desc': $query->orderBy('created_at', 'desc');
                                break;
                            case 'order_asc': $query->orderBy('order_number', 'asc');
                                break;
                            case 'order_desc': $query->orderBy('order_number', 'desc');
                                break;
                            default: $query->latest();
                                break;
                        }
                    } else {
                        $query->orderBy('fulfill_at', 'asc');
                    }
                })
                ->make(true);
        }

        // Render Awal Halaman Blade (Hanya berjalan saat Non-AJAX)
        $query = Order::with(['items', 'payments'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                $q->where('payment_method', '!=', 'manual_wa')->orWhereNull('payment_method');
            })
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('fulfill_at', $request->date);
        }
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'ALL') {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(15)->withQueryString();

        // ⚡ Hitung 3 statistik sekaligus dalam 1 Query Tunggal untuk Render Blade Awal
        $statsData = Order::whereNotIn('status', ['completed', 'cancelled'])
            ->where(fn ($q) => $q->where('payment_method', '!=', 'manual_wa')->orWhereNull('payment_method'))
            ->selectRaw("
                COUNT(CASE WHEN DATE(fulfill_at) = CURRENT_DATE() THEN 1 END) as today_po,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_po,
                COUNT(CASE WHEN status = 'preparing' THEN 1 END) as preparing_po
            ")->first();

        $todayPO = $statsData->today_po ?? 0;
        $pendingPO = $statsData->pending_po ?? 0;
        $preparingPO = $statsData->preparing_po ?? 0;

        return view('admin.po.index', compact('orders', 'todayPO', 'pendingPO', 'preparingPO'));
    }

    /**
     * 💬 HALAMAN ORDER MANUAL (KHUSUS WHATSAPP / MANUAL TRANSFER)
     */
    public function manualOrders(Request $request)
    {
        $query = $this->orderService->getManualOrdersQuery($request);

        if ($request->ajax()) {
            // ⚡ Hitung statistik realtime khusus manual_wa dalam 1 Query Tunggal
            $statsData = Order::where('payment_method', 'manual_wa')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->selectRaw("
                    COUNT(CASE WHEN payment_status IN ('unpaid', 'pending') OR payment_status IS NULL THEN 1 END) as unverified,
                    COUNT(CASE WHEN payment_status IN ('paid', 'lunas', 'dp', 'partial') THEN 1 END) as verified,
                    COUNT(*) as total
                ")->first();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', fn ($row) => is_object($row->status) ? ($row->status->value ?? (string) $row->status) : (string) $row->status)
                ->editColumn('payment_plan', fn ($row) => is_object($row->payment_plan) ? ($row->payment_plan->value ?? (string) $row->payment_plan) : (string) $row->payment_plan)
                ->editColumn('payment_status', fn ($row) => is_object($row->payment_status) ? ($row->payment_status->value ?? (string) $row->payment_status) : (string) $row->payment_status)
                ->editColumn('order_type', fn ($row) => is_object($row->order_type) ? ($row->order_type->value ?? (string) $row->order_type) : (string) $row->order_type)
                ->editColumn('created_at', fn ($row) => $row->created_at ? Carbon::parse($row->created_at)->translatedFormat('d M Y, H:i') : '-')
                ->editColumn('fulfill_at', fn ($row) => $row->fulfill_at ? Carbon::parse($row->fulfill_at)->translatedFormat('d M Y, H:i') : '-')
                ->editColumn('total_amount', fn ($row) => 'Rp '.number_format((float) $row->total_amount, 0, ',', '.'))
                ->addColumn('payment_proof_history', fn ($row) => $this->proofService->getProofHistoryFiles($row->order_number))
                ->with([
                    'stat_unverified' => $statsData->unverified ?? 0,
                    'stat_verified' => $statsData->verified ?? 0,
                    'stat_total' => $statsData->total ?? 0,
                ])
                ->make(true);
        }

        $orders = $query->paginate(15)->withQueryString();

        $orders->getCollection()->transform(function ($order) {
            $historyFiles = $this->proofService->getProofHistoryFiles($order->order_number);
            $order->payment_proof_history = $historyFiles;

            if (empty($order->payment_proof) && ! empty($historyFiles)) {
                $latest = end($historyFiles);
                $order->payment_proof = $latest['file'];
            }

            return $order;
        });

        return view('admin.orders.manual', compact('orders'));
    }

    /**
     * 📸 MENGUNGGAH BUKTI TRANSFER DARI SISI CUSTOMER/FRONTEND
     */
    public function uploadProof(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'payment_proof.required' => 'Silakan pilih file foto bukti transfer.',
            'payment_proof.image' => 'File bukti transfer harus berupa gambar.',
            'payment_proof.mimes' => 'Format gambar yang diperbolehkan hanya JPG, JPEG, PNG, atau WebP.',
            'payment_proof.max' => 'Ukuran file gambar maksimal 5MB.',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($request->hasFile('payment_proof')) {
            $fileName = $this->proofService->storeProof($order, $request->file('payment_proof'));
            $order->update(['payment_proof' => $fileName]);

            event(new OrderUpdated);
        }

        return back()->with('success', 'Bukti transfer untuk order '.$order->order_number.' berhasil diunggah. Tim kami akan segera memverifikasinya.');
    }

    /**
     * 📜 HALAMAN HISTORY & ARSIP PESANAN
     */
    public function history(Request $request)
    {
        $query = $this->orderService->getHistoryOrdersQuery($request);

        if ($request->ajax()) {
            // ⚡ Hitung statistik agregat history dalam 1 Query Tunggal
            $historyStats = Order::selectRaw("
                COUNT(CASE WHEN status IN ('completed', 'cancelled') THEN 1 END) as total_history,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
                SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue
            ")->first();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('placed_date_formatted', function ($row) {
                    $date = $row->placed_at ?? $row->created_at;

                    return $date ? Carbon::parse($date)->translatedFormat('d M Y') : '-';
                })
                ->addColumn('placed_time_formatted', function ($row) {
                    $date = $row->placed_at ?? $row->created_at;

                    return $date ? Carbon::parse($date)->format('H:i') : '-';
                })
                ->addColumn('fulfill_date_formatted', function ($row) {
                    return $row->fulfill_at ? Carbon::parse($row->fulfill_at)->translatedFormat('d M Y') : null;
                })
                ->addColumn('fulfill_time_formatted', function ($row) {
                    return $row->fulfill_at ? Carbon::parse($row->fulfill_at)->format('H:i') : null;
                })
                ->editColumn('status', fn ($row) => is_object($row->status) ? ($row->status->value ?? (string) $row->status) : (string) $row->status)
                ->editColumn('payment_status', fn ($row) => is_object($row->payment_status) ? ($row->payment_status->value ?? (string) $row->payment_status) : (string) $row->payment_status)
                ->editColumn('order_type', fn ($row) => is_object($row->order_type) ? ($row->order_type->value ?? (string) $row->order_type) : (string) $row->order_type)
                ->with([
                    'totalHistoryCount' => $historyStats->total_history ?? 0,
                    'completedCount' => $historyStats->completed_count ?? 0,
                    'cancelledCount' => $historyStats->cancelled_count ?? 0,
                    'totalRevenue' => (float) ($historyStats->total_revenue ?? 0),
                ])
                ->make(true);
        }

        $orders = $query->paginate(15)->withQueryString();

        $historyStats = Order::selectRaw("
            COUNT(CASE WHEN status IN ('completed', 'cancelled') THEN 1 END) as total_history,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
            SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue
        ")->first();

        $totalHistoryCount = $historyStats->total_history ?? 0;
        $completedCount = $historyStats->completed_count ?? 0;
        $cancelledCount = $historyStats->cancelled_count ?? 0;
        $totalRevenue = $historyStats->total_revenue ?? 0;

        return view('admin.orders.history', compact(
            'orders',
            'totalHistoryCount',
            'completedCount',
            'cancelledCount',
            'totalRevenue'
        ));
    }

    /**
     * 🛠️ VERIFIKASI PEMBAYARAN MANUAL (TRANSFER / WA)
     */
    public function verifyPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $this->orderService->verifyPayment($order);

        event(new OrderUpdated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran order '.$order->order_number.' berhasil diverifikasi & dikonfirmasi.',
            ]);
        }

        return back()->with('success', 'Pembayaran order '.$order->order_number.' berhasil diverifikasi & dikonfirmasi.');
    }

    /**
     * Update Status Pengerjaan Pesanan (Dapur)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $this->orderService->updateOrderStatus($order, $request->status);

        event(new OrderUpdated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan '.$order->order_number.' berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'Status pesanan '.$order->order_number.' berhasil diperbarui.');
    }

    /**
     * 💵 Update Status Pelunasan Offline
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid,refunded',
        ]);

        $order = Order::findOrFail($id);
        $this->orderService->updatePaymentStatus($order, $request->payment_status);

        event(new OrderUpdated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran '.$order->order_number.' berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'Status pembayaran '.$order->order_number.' berhasil diperbarui.');
    }
}