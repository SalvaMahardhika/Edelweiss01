<?php

namespace App\Http\Controllers;

use App\Events\OrderUpdated;
use App\Helpers\ImageHelper;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    // ==========================================
    // HELPER METHOD PRIVAT (DYNAMIC HOSTING PATH & PROOF HISTORY SCANNER)
    // ==========================================

    private function getBuktiTfPath(): string
    {
        $publicHtmlPath = base_path('../public_html');

        if (file_exists($publicHtmlPath)) {
            return $publicHtmlPath.'/img/buktitf';
        }

        return public_path('img/buktitf');
    }

    private function getProofHistoryFiles(string $orderNumber): array
    {
        $targetFolder = $this->getBuktiTfPath();

        if (! file_exists($targetFolder)) {
            return [];
        }

        $pattern = $targetFolder.'/'.$orderNumber.'-*.*';
        $files = glob($pattern);

        if (empty($files) || ! is_array($files)) {
            return [];
        }

        sort($files);

        $historyFormatted = [];
        foreach ($files as $index => $filePath) {
            $fileName = basename($filePath);
            $historyFormatted[] = [
                'url' => asset('img/buktitf/'.$fileName),
                'file' => $fileName,
                'sequence' => $index + 1,
                'uploaded_at' => date('d M Y, H:i', filemtime($filePath)).' WIB',
            ];
        }

        return $historyFormatted;
    }

    // 📅 Halaman Utama Jadwal PO (KHUSUS PAYMENT GATEWAY)
    public function index(Request $request)
    {
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

        if ($request->ajax()) {
            $query = Order::with(['items', 'payments'])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'manual_wa')
                        ->orWhereNull('payment_method');
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

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $statusVal = is_object($row->status) ? ($row->status->value ?? (string) $row->status) : (string) $row->status;
                    $statusClasses = [
                        'pending' => 'bg-amber-100 text-amber-800',
                        'confirmed' => 'bg-blue-100 text-blue-800',
                        'preparing' => 'bg-purple-100 text-purple-800',
                        'ready' => 'bg-emerald-100 text-emerald-800',
                    ];
                    $cls = $statusClasses[$statusVal] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2.5 py-1 text-xs font-bold rounded-lg '.$cls.'">'.ucfirst($statusVal).'</span>';
                })
                ->editColumn('payment_status', function ($row) {
                    $payVal = is_object($row->payment_status) ? ($row->payment_status->value ?? (string) $row->payment_status) : (string) $row->payment_status;
                    $payClasses = [
                        'unpaid' => 'bg-rose-100 text-rose-800',
                        'partial' => 'bg-amber-100 text-amber-800',
                        'paid' => 'bg-emerald-100 text-emerald-800',
                        'refunded' => 'bg-gray-100 text-gray-800',
                    ];
                    $cls = $payClasses[$payVal] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2.5 py-1 text-xs font-bold rounded-lg '.$cls.'">'.ucfirst($payVal).'</span>';
                })
                ->editColumn('fulfill_at', function ($row) {
                    return $row->fulfill_at ? date('d M Y, H:i', strtotime($row->fulfill_at)).' WIB' : '-';
                })
                ->editColumn('total_amount', function ($row) {
                    return 'Rp '.number_format((float)$row->total_amount, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" onclick="viewOrderDetail('.$row->id.')" class="p-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition text-xs font-bold">
                            <i class="fa-solid fa-eye"></i> Detail
                        </button>
                    ';
                })
                ->rawColumns(['status', 'payment_status', 'action'])
                ->make(true);
        }

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

    // 💬 HALAMAN ORDER MANUAL (KHUSUS WHATSAPP / MANUAL TRANSFER)
    public function manualOrders(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with(['items'])
                ->where(function ($q) {
                    $q->whereIn('payment_method', ['manual_wa', 'manual_bank', 'manual'])
                        ->orWhereNull('payment_method');
                })
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest();

            if ($request->filled('search')) {
                $search = is_array($request->search) ? ($request->search['value'] ?? '') : $request->search;
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_phone', 'like', "%{$search}%")
                            ->orWhere('customer_email', 'like', "%{$search}%");
                    });
                }
            }

            // 🟢 PERBAIKAN 1: TAMBAHKAN FILTER STATUS PRODUKSI (STATUS PESANAN) VIA AJAX
            if ($request->filled('status') && $request->status !== 'ALL') {
                $query->where('status', $request->status);
            }

            if ($request->filled('status_bayar') && $request->status_bayar !== 'ALL') {
                $query->where('payment_status', $request->status_bayar);
            }

            if ($request->filled('has_proof')) {
                if ($request->has_proof === '1') {
                    $query->whereNotNull('payment_proof');
                } elseif ($request->has_proof === '0') {
                    $query->whereNull('payment_proof');
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return is_object($row->status) ? ($row->status->value ?? (string) $row->status) : (string) $row->status;
                })
                ->editColumn('payment_plan', function ($row) {
                    return is_object($row->payment_plan) ? ($row->payment_plan->value ?? (string) $row->payment_plan) : (string) $row->payment_plan;
                })
                ->editColumn('payment_status', function ($row) {
                    return is_object($row->payment_status) ? ($row->payment_status->value ?? (string) $row->payment_status) : (string) $row->payment_status;
                })
                ->editColumn('order_type', function ($row) {
                    return is_object($row->order_type) ? ($row->order_type->value ?? (string) $row->order_type) : (string) $row->order_type;
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->translatedFormat('d M Y, H:i') : '-';
                })
                ->editColumn('fulfill_at', function ($row) {
                    return $row->fulfill_at ? \Carbon\Carbon::parse($row->fulfill_at)->translatedFormat('d M Y, H:i') : '-';
                })
                ->editColumn('total_amount', function ($row) {
                    return 'Rp '.number_format((float)$row->total_amount, 0, ',', '.');
                })
                ->addColumn('payment_proof_history', function ($row) {
                    return $this->getProofHistoryFiles($row->order_number);
                })
                ->make(true);
        }

        $query = Order::with(['items'])
            ->where(function ($q) {
                $q->whereIn('payment_method', ['manual_wa', 'manual_bank', 'manual'])
                    ->orWhereNull('payment_method');
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // 🟢 PERBAIKAN 2: TAMBAHKAN FILTER STATUS PRODUKSI (STATUS PESANAN) UNTUK NON-AJAX / PAGE LOAD INITIAL
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('status_bayar') && $request->status_bayar !== 'ALL') {
            $query->where('payment_status', $request->status_bayar);
        }

        if ($request->filled('has_proof')) {
            if ($request->has_proof === '1') {
                $query->whereNotNull('payment_proof');
            } elseif ($request->has_proof === '0') {
                $query->whereNull('payment_proof');
            }
        }

        $orders = $query->paginate(15)->withQueryString();

        $orders->getCollection()->transform(function ($order) {
            $historyFiles = $this->getProofHistoryFiles($order->order_number);
            $order->payment_proof_history = $historyFiles;

            if (empty($order->payment_proof) && ! empty($historyFiles)) {
                $latest = end($historyFiles);
                $order->payment_proof = $latest['file'];
            }

            return $order;
        });

        return view('admin.orders.manual', compact('orders'));
    }

    // 📸 MENGUNGGAH BUKTI TRANSFER DARI SISI CUSTOMER/FRONTEND
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
            $targetFolder = $this->getBuktiTfPath();

            if (! file_exists($targetFolder)) {
                mkdir($targetFolder, 0755, true);
            }

            $existingHistory = $this->getProofHistoryFiles($order->order_number);
            $nextSequence = count($existingHistory) + 1;
            $sequenceStr = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

            $fileName = $order->order_number.'-'.$sequenceStr.'.webp';
            $destinationPath = $targetFolder.'/'.$fileName;

            try {
                ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 80, 1200);
            } catch (Exception $e) {
                $fileName = $order->order_number.'-'.$sequenceStr.'.'.$file->getClientOriginalExtension();
                $file->move($targetFolder, $fileName);
            }

            $order->update([
                'payment_proof' => $fileName,
            ]);

            // 🔴 BROADCAST REVERB EVENT SAAT BUKTI TF DIUNGGAH
            broadcast(new OrderUpdated());
        }

        return back()->with('success', 'Bukti transfer untuk order '.$order->order_number.' berhasil diunggah. Tim kami akan segera memverifikasinya.');
    }

    // 📜 HALAMAN HISTORY & ARSIP PESANAN
    public function history(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with(['items', 'payments'])->latest();

            if ($request->filled('status') && $request->status !== 'ALL') {
                $query->where('status', $request->status);
            } else {
                $query->whereIn('status', ['completed', 'cancelled']);
            }

            if ($request->filled('placed_date')) {
                $query->where(function ($q) use ($request) {
                    $q->whereDate('placed_at', $request->placed_date)
                        ->orWhereDate('created_at', $request->placed_date);
                });
            }

            if ($request->filled('fulfill_date')) {
                $query->whereDate('fulfill_at', $request->fulfill_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $statusVal = is_object($row->status) ? ($row->status->value ?? (string) $row->status) : (string) $row->status;
                    $badge = $statusVal === 'completed'
                        ? '<span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-800">Selesai</span>'
                        : '<span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-100 text-rose-800">Batal</span>';
                    return $badge;
                })
                ->editColumn('total_amount', function ($row) {
                    return 'Rp '.number_format((float)$row->total_amount, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" onclick="viewOrderDetail('.$row->id.')" class="p-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition text-xs font-bold">
                            <i class="fa-solid fa-eye"></i> Detail
                        </button>
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $query = Order::with(['items', 'payments'])->latest();

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('placed_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('placed_at', $request->placed_date)
                    ->orWhereDate('created_at', $request->placed_date);
            });
        }

        if ($request->filled('fulfill_date')) {
            $query->whereDate('fulfill_at', $request->fulfill_date);
        }

        $orders = $query->paginate(15)->withQueryString();

        $totalHistoryCount = Order::whereIn('status', ['completed', 'cancelled'])->count();
        $completedCount = Order::where('status', 'completed')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();
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

        $paymentPlanVal = is_object($order->payment_plan) ? ($order->payment_plan->value ?? (string) $order->payment_plan) : (string) $order->payment_plan;

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

        // 🔴 BROADCAST REVERB EVENT
        broadcast(new OrderUpdated());

        return back()->with('success', 'Pembayaran order '.$order->order_number.' berhasil diverifikasi & dikonfirmasi.');
    }

    // Update Status Pengerjaan Pesanan (Dapur)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);

        $updateData = ['status' => $request->status];

        if ($request->status === 'completed') {
            $updateData['payment_status'] = 'paid';
            $updateData['amount_paid'] = $order->total_amount;
        }

        $order->update($updateData);

        // 🔴 BROADCAST REVERB EVENT
        broadcast(new OrderUpdated());

        return back()->with('success', 'Status pesanan '.$order->order_number.' berhasil diperbarui.');
    }

    // 💵 Update Status Pelunasan Offline
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid,refunded',
        ]);

        $order = Order::findOrFail($id);

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

        // 🔴 BROADCAST REVERB EVENT
        broadcast(new OrderUpdated());

        return back()->with('success', 'Status pembayaran '.$order->order_number.' berhasil diperbarui.');
    }
}