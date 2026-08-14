<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentStatus;
use App\Events\OrderUpdated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Inject PaymentProofService untuk mengelola berkas bukti transfer
     */
    public function __construct(
        protected PaymentProofService $proofService
    ) {}

    // ==========================================
    // 1. LOGIKA CREATION & KALKULASI PESANAN (CUSTOMER / FRONTEND & OFFLINE REKAP)
    // ==========================================

    /**
     * Membuat Order baru dengan menghitung subtotal & total_amount langsung dari harga produk di database.
     * Mengabaikan harga apapun yang dikirimkan dari browser.
     *
     * @param  array  $itemsData  Array berisi [['product_id' => x, 'quantity' => y], ...]
     */
    public function createOrder(array $orderData, array $itemsData): Order
    {
        return DB::transaction(function () use ($orderData, $itemsData) {
            // 1. Tentukan nomor order unik jika belum diset
            $orderNumber = $orderData['order_number'] ?? 'EDL-'.now()->format('Ymd').'-'.rand(1000, 9999);

            // 2. Buat instance Order dengan nilai default, pastikan status awal pending
            $order = Order::create(array_merge($orderData, [
                'order_number' => $orderNumber,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'subtotal' => 0.00,
                'tax_amount' => 0.00,
                'total_amount' => 0.00,
                'dp_amount' => 0.00,
                'amount_paid' => 0.00,
            ]));

            $subtotal = '0.00';

            // 3. Masukkan item-item belanjaan dengan harga asli dari DB produk
            foreach ($itemsData as $itemData) {
                $product = Produk::findOrFail($itemData['product_id']);

                // Ambil harga fresh dari database, bukan dari input browser/klien
                $price = $product->harga;
                $qty = $itemData['quantity'];
                $itemSubtotal = bcmul((string) $price, (string) $qty, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $subtotal = bcadd($subtotal, $itemSubtotal, 2);
            }

            // 4. Hitung pajak 11% dan total pembayaran
            $taxAmount = bcmul($subtotal, '0.11', 2);
            $totalAmount = bcadd($subtotal, $taxAmount, 2);

            // 5. Tentukan dp_amount (50%) jika menggunakan paket DP
            $paymentPlan = $orderData['payment_plan'] ?? PaymentPlan::Full;
            $dpAmount = ($paymentPlan === PaymentPlan::Dp || $paymentPlan === 'dp')
                ? bcmul($totalAmount, '0.50', 2)
                : '0.00';

            // 6. Update order dengan total yang dihitung di server
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'dp_amount' => $dpAmount,
            ]);

            // Jika status final ditentukan pada parameter input, set setelah order ter-update & paid dihitung
            if (isset($orderData['status'])) {
                $order->status = $orderData['status'];
                $order->save();
            }

            return $order;
        });
    }

    /**
     * 📝 Membuat Order Offline / Rekap Lampau (Direct Store Sales)
     * 
     * Menerima custom price per item, penanganan email dummy otomatis jika kosong, 
     * serta mencatat transaksi langsung berstatus 'completed' & 'paid'.
     *
     * @param  array  $data  Payload ter-validasi dari OfflineOrderController
     * @param  int  $adminUserId ID User Admin Kasir yang meng-input
     */
    public function createOfflineOrder(array $data, int $adminUserId): Order
    {
        return DB::transaction(function () use ($data, $adminUserId) {
            // 1. Format Waktu Pemesanan & Pengambilan (Langsung parse string datetime 'Y-m-d H:i:s')
            $placedAt = Carbon::parse($data['placed_at']);
            $fulfillAt = Carbon::parse($data['fulfill_at']);

            // 2. Auto Email Dummy jika input kosong
            $customerEmail = ! empty($data['customer_email'])
                ? $data['customer_email']
                : 'offline.'.$placedAt->format('YmdHis').'.'.Str::random(4).'@edelweiss.internal';

            // 3. Generate Order Number Unik Format OFF-
            $orderNumber = 'OFF-'.$placedAt->format('Ymd').'-'.rand(1000, 9999);

            // 4. Hitung Subtotal berdasarkan Custom Price per item dari input form
            $subtotal = '0.00';
            $itemsToCreate = [];

            foreach ($data['items'] as $itemData) {
                $product = Produk::findOrFail($itemData['product_id']);

                $customPrice = (string) $itemData['price'];
                $qty = (string) $itemData['quantity'];
                $itemSubtotal = bcmul($customPrice, $qty, 2);

                $subtotal = bcadd($subtotal, $itemSubtotal, 2);

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'unit_price' => $customPrice,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                    'notes' => $itemData['notes'] ?? null,
                ];
            }

            // 5. Hitung DP Penanda jika skema DP dipilih
            $dpAmount = ($data['payment_plan'] === 'dp')
                ? bcmul($subtotal, '0.50', 2)
                : '0.00';

            // 6. Buat Record Order (Direct 'completed' & 'paid')
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $adminUserId,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $customerEmail,
                'order_type' => $data['order_type'],
                'delivery_address' => $data['order_type'] === 'delivery' ? ($data['delivery_address'] ?? null) : null,
                'status' => 'completed',
                'payment_method' => 'offline_store',
                'payment_plan' => $data['payment_plan'],
                'payment_status' => 'paid',
                'subtotal' => $subtotal,
                'tax_amount' => '0.00',
                'total_amount' => $subtotal,
                'dp_amount' => $dpAmount,
                'amount_paid' => $subtotal,
                'placed_at' => $placedAt,
                'fulfill_at' => $fulfillAt,
                'notes' => $data['notes'] ?? null,
            ]);

            // 7. Simpan Order Items
            foreach ($itemsToCreate as $item) {
                OrderItem::create(array_merge($item, [
                    'order_id' => $order->id,
                ]));
            }

            // 8. Broadcast Event Realtime Update
            broadcast(new OrderUpdated);

            return $order;
        });
    }

    // ==========================================
    // 2. LOGIKA MANAJEMEN ADMIN & QUERY DATATABLES
    // ==========================================

    /**
     * Membatalkan otomatis order unpaid yang sudah kedaluwarsa (> 1 hari)
     */
    public function autoCancelExpiredOrders(): void
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
            ->update(['status' => 'cancelled']);
    }

    /**
     * Query dasar untuk Order Manual (WhatsApp / Transfer Bank Manual)
     */
    public function getManualOrdersQuery(Request $request): Builder
    {
        $query = Order::with(['items'])
            ->where(function ($q) {
                $q->whereIn('payment_method', ['manual_wa', 'manual_bank', 'manual'])
                    ->orWhereNull('payment_method');
            })
            ->whereNotIn('status', ['completed', 'cancelled']);

        if ($request->filled('search')) {
            $search = is_array($request->search) ? ($request->search['value'] ?? '') : $request->search;
            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            }
        }

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

        return $query;
    }

    /**
     * Query dasar untuk History Order (Status Selesai / Batal)
     * Catatan: ->latest() dilepas agar DataTables Server-Side bisa melakukan sorting dinamis
     */
    public function getHistoryOrdersQuery(Request $request): Builder
    {
        $query = Order::with(['items', 'payments']);

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        if ($request->filled('search')) {
            $search = is_array($request->search) ? ($request->search['value'] ?? '') : $request->search;
            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            }
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

        return $query;
    }

    /**
     * Verifikasi Pembayaran Manual (Sesuai Skema DP 50% atau Full Payment)
     */
    public function verifyPayment(Order $order): void
    {
        $paymentPlanVal = is_object($order->payment_plan)
            ? ($order->payment_plan->value ?? (string) $order->payment_plan)
            : (string) $order->payment_plan;

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

        broadcast(new OrderUpdated);
    }

    /**
     * Perbarui Status Pengerjaan/Produksi Pesanan
     */
    public function updateOrderStatus(Order $order, string $status): void
    {
        $updateData = ['status' => $status];

        if ($status === 'completed') {
            $updateData['payment_status'] = 'paid';
            $updateData['amount_paid'] = $order->total_amount;
        }

        $order->update($updateData);

        broadcast(new OrderUpdated);
    }

    /**
     * Perbarui Status Pelunasan Offline
     */
    public function updatePaymentStatus(Order $order, string $paymentStatus): void
    {
        $amountPaid = $order->amount_paid;

        if ($paymentStatus === 'paid') {
            $amountPaid = $order->total_amount;
        } elseif ($paymentStatus === 'unpaid') {
            $amountPaid = 0;
        } elseif ($paymentStatus === 'partial') {
            $amountPaid = $order->dp_amount ?? ($order->total_amount * 0.5);
        }

        $order->update([
            'payment_status' => $paymentStatus,
            'amount_paid' => $amountPaid,
        ]);

        broadcast(new OrderUpdated);
    }
}