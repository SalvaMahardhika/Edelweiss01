<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // 📅 Halaman Utama Jadwal PO (KHUSUS PAYMENT GATEWAY)
    public function index(Request $request)
    {
        // 1. 🚨 OTOMATIS BATALKAN PESANAN UNPAID > 1 HARI (24 JAM)
        Order::where('payment_status', 'unpaid')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                $q->where('placed_at', '<=', now()->subDay())
                    ->orWhere(function ($subQ) {
                        $subQ->whereNull('placed_at')
                            ->where('created_at', '<=', now()->subDay());
                    });
            })
            ->update([
                'status' => 'cancelled',
            ]);

        // 2. 📋 QUERY UTAMA JADWAL PO (HANYA PAYMENT GATEWAY & EXCLUDE 'completed'/'cancelled')
        $query = Order::with(['items', 'payments'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                // Tampilkan hanya yang payment_gateway (atau null/default lama)
                $q->where('payment_method', '!=', 'manual_wa')
                    ->orWhereNull('payment_method');
            })
            ->latest();

        // Filter berdasarkan tanggal Fulfill/Siap
        if ($request->filled('date')) {
            $query->whereDate('fulfill_at', $request->date);
        }

        // Filter Status Order (Hanya untuk status aktif)
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        // Filter Status Pembayaran
        if ($request->filled('payment_status') && $request->payment_status !== 'ALL') {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Rekap ringkasan pesanan aktif Payment Gateway
        $todayPO = Order::whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                $q->where('payment_method', '!=', 'manual_wa')->orWhereNull('payment_method');
            })
            ->whereDate('fulfill_at', now())
            ->count();

        $pendingPO = Order::whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                $q->where('payment_method', '!=', 'manual_wa')->orWhereNull('payment_method');
            })
            ->where('status', 'pending')
            ->count();

        $preparingPO = Order::whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                $q->where('payment_method', '!=', 'manual_wa')->orWhereNull('payment_method');
            })
            ->where('status', 'preparing')
            ->count();

        return view('admin.po.index', compact('orders', 'todayPO', 'pendingPO', 'preparingPO'));
    }

    // 💬 🆕 HALAMAN ORDER MANUAL (KHUSUS WHATSAPP / MANUAL TRANSFER)
    public function manualOrders(Request $request)
    {
        $query = Order::with(['items', 'payments'])
            ->where('payment_method', 'manual_wa')
            ->whereNotIn('status', ['completed', 'cancelled']) // 🟢 Pesanan Selesai / Batal otomatis disembunyikan & masuk ke History
            ->latest();

        // 🔍 Filter Pencarian Keyword (No. Order, Nama, No. HP, Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // 💵 Filter Status Pembayaran
        if ($request->filled('status_bayar')) {
            $query->where('payment_status', $request->status_bayar);
        }

        // 🖼️ Filter Status Bukti Transfer (Ada / Belum Ada Upload)
        if ($request->filled('has_proof')) {
            if ($request->has_proof === '1') {
                $query->whereNotNull('payment_proof');
            } elseif ($request->has_proof === '0') {
                $query->whereNull('payment_proof');
            }
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.manual', compact('orders'));
    }

    // 📸 🆕 MENGUNGGAH BUKTI TRANSFER DARI SISI CUSTOMER/FRONTEND (WITH WEBP CONVERSION)
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
            $file = $request->file('payment_proof');

            // Hapus bukti lama jika pengguna melakukan re-upload
            if ($order->payment_proof && file_exists(public_path('img/buktitf/'.$order->payment_proof))) {
                @unlink(public_path('img/buktitf/'.$order->payment_proof));
            }

            // Path Folder Tujuan
            $targetFolder = public_path('img/buktitf');
            if (! file_exists($targetFolder)) {
                mkdir($targetFolder, 0755, true);
            }

            // Nama file baru wajib menggunakan format ekstensi .webp
            $fileName = 'tf_'.$order->order_number.'_'.time().'.webp';
            $destinationPath = $targetFolder.'/'.$fileName;

            try {
                // 🚀 Konversi gambar ke format WebP menggunakan ImageHelper
                ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 80, 1200);
            } catch (Exception $e) {
                // Fallback: Jika konversi GD gagal, simpan file secara standar
                $fileName = 'tf_'.$order->order_number.'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move($targetFolder, $fileName);
            }

            // Simpan nama file ke kolom payment_proof di database
            $order->update([
                'payment_proof' => $fileName,
            ]);
        }

        return back()->with('success', 'Bukti transfer untuk order '.$order->order_number.' berhasil diunggah. Tim kami akan segera memverifikasinya.');
    }

    // 📜 🆕 HALAMAN HISTORY & ARSIP PESANAN (MENGGABUNGKAN SEMUA METODE)
    public function history(Request $request)
    {
        // Query Dasar: Ambil pesanan yang berstatus 'completed' atau 'cancelled'
        $query = Order::with(['items', 'payments'])->latest();

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        // 🔍 Pencarian Keyword (No. Order, Nama, No. HP, Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // 📅 Filter Tanggal Pemesanan (placed_at / created_at)
        if ($request->filled('placed_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('placed_at', $request->placed_date)
                    ->orWhereDate('created_at', $request->placed_date);
            });
        }

        // 🚚 Filter Tanggal Pengambilan/Kirim (fulfill_at)
        if ($request->filled('fulfill_date')) {
            $query->whereDate('fulfill_at', $request->fulfill_date);
        }

        // Pagination data history
        $orders = $query->paginate(15)->withQueryString();

        // 📊 Rekap Statistik untuk Kartu Ringkasan
        $totalHistoryCount = Order::whereIn('status', ['completed', 'cancelled'])->count();
        $completedCount = Order::where('status', 'completed')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();

        // 🟢 PERBAIKAN RUMUS OMZET:
        // Hanya menghitung transaksi berstatus 'completed' (pesanan batal tidak dihitung)
        // Dan menggunakan sum('total_amount') agar pesanan DP yang sudah completed langsung terhitung nilai penuh (100%)
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');

        return view('admin.orders.history', compact(
            'orders',
            'totalHistoryCount',
            'completedCount',
            'cancelledCount',
            'totalRevenue'
        ));
    }

    // 🛠️ VERIFIKASI PEMBAYARAN MANUAL (TRANSFER / WA)
    public function verifyPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Ambil string skema pembayaran (dp/full)
        $paymentPlanVal = is_object($order->payment_plan) ? ($order->payment_plan->value ?? (string) $order->payment_plan) : (string) $order->payment_plan;

        // Jika skema DP 50%, ubah status ke 'partial' (Enum valid untuk DP), jika Full ubah ke 'paid'
        if (strtolower($paymentPlanVal) === 'dp') {
            $order->update([
                'payment_status' => 'partial',
                'amount_paid' => $order->dp_amount ?? ($order->total_amount * 0.5),
                'status' => 'confirmed',
            ]);
        } else {
            $order->update([
                'payment_status' => 'paid',
                'amount_paid' => $order->total_amount,
                'status' => 'confirmed',
            ]);
        }

        return back()->with('success', 'Pembayaran order '.$order->order_number.' berhasil diverifikasi & dikonfirmasi.');
    }

    // Update Status Pengerjaan Pesanan
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);

        $updateData = ['status' => $request->status];

        // 🟢 Otomatis tandai pembayaran sebagai 'paid' (Lunas) bila pesanan diselesaikan (completed)
        if ($request->status === 'completed') {
            $updateData['payment_status'] = 'paid';
            $updateData['amount_paid'] = $order->total_amount;
        }

        $order->update($updateData);

        return back()->with('success', 'Status pesanan '.$order->order_number.' berhasil diperbarui.');
    }

    // Update Status Pelunasan Manual (Melayani pelunasan sisa DP secara tunai/offline saat pickup atau delivery)
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid,refunded',
        ]);

        $order = Order::findOrFail($id);

        // Jika status diubah ke 'paid' (lunas offline), set amount_paid ke total_amount
        $amountPaid = $order->amount_paid;
        if ($request->payment_status === 'paid') {
            $amountPaid = $order->total_amount;
        } elseif ($request->payment_status === 'unpaid') {
            $amountPaid = 0;
        } elseif ($request->payment_status === 'partial') {
            $amountPaid = $order->dp_amount ?? ($order->total_amount * 0.5);
        }

        $order->update([
            'payment_status' => $request->payment_status,
            'amount_paid' => $amountPaid,
        ]);

        return back()->with('success', 'Status pembayaran '.$order->order_number.' berhasil diperbarui.');
    }
}
