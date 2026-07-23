<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Services\ScalevService; // 🟢 Menggunakan ScalevService menggantikan MidtransService
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Tampilkan halaman formulir checkout untuk user yang sudah login.
     */
    public function index()
    {
        // Ambil data user yang sedang login untuk auto-fill form
        $user = auth()->user();

        // Pastikan user terautentikasi
        if (! $user) {
            return redirect()->route('login');
        }

        return view('orders.checkout', compact('user'));
    }

    /**
     * Proses simpan order baru ke database & langsung alihkan ke Scalev.
     */
    public function store(Request $request, ScalevService $scalevService)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'order_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:order_type,delivery|nullable|string',
            'payment_plan' => 'required|in:full,dp',
            'fulfill_at' => 'required|date|after_or_equal:'.now()->addHours(2)->format('Y-m-d H:i:s'),
            'notes' => 'nullable|string',
            'cart_items' => 'required|json',
        ], [
            'fulfill_at.after_or_equal' => 'Waktu kesiapan pesanan minimal 2 jam dari sekarang.',
        ]);

        $cartData = json_decode($request->cart_items, true);
        if (empty($cartData)) {
            return redirect()->back()->withErrors(['cart_items' => 'Keranjang Anda kosong.']);
        }

        try {
            DB::beginTransaction();

            // 1. Hitung finansial order berdasarkan data database asli
            $subtotal = 0;
            $itemsToSave = [];

            foreach ($cartData as $item) {
                $product = Produk::findOrFail($item['id']);

                $itemSubtotal = $product->harga * $item['quantity'];
                $subtotal += $itemSubtotal;

                $itemsToSave[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'unit_price' => $product->harga,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            // 2. Hitung Pajak/PPN 11% secara presisi
            $taxAmount = bcmul((string) $subtotal, '0.11', 2);
            $totalAmount = bcadd((string) $subtotal, (string) $taxAmount, 2);

            // Atur skema uang muka (DP 50%)
            $dpAmount = ($request->payment_plan === 'dp') ? ($totalAmount * 0.5) : 0;

            // 3. Generate Nomor Order Unik (cth: EDL-20260723-0001)
            $dateString = now()->format('Ymd');
            $latestOrder = Order::where('order_number', 'LIKE', "EDL-{$dateString}-%")->latest()->first();
            $nextSequence = $latestOrder ? ((int) Str::afterLast($latestOrder->order_number, '-') + 1) : 1;
            $orderNumber = "EDL-{$dateString}-".str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            // 4. Simpan ke tabel orders
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'order_type' => $request->order_type,
                'delivery_address' => $request->delivery_address,
                'status' => 'pending',
                'payment_plan' => $request->payment_plan,
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'dp_amount' => $dpAmount,
                'amount_paid' => 0,
                'fulfill_at' => $request->fulfill_at,
                'settlement_due_at' => $request->payment_plan === 'dp' ? Carbon::parse($request->fulfill_at)->subDays(1) : null,
                'notes' => $request->notes,
                'placed_at' => now(),
            ]);

            // 5. Simpan ke tabel order_items
            foreach ($itemsToSave as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            DB::commit();

            // 🟢 6. Dapatkan URL Pembayaran dari Scalev & Alihkan Pelanggan
            $paymentUrl = $scalevService->createPaymentUrl($order);

            return redirect()->away($paymentUrl);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal memproses Pre-Order: '.$e->getMessage()]);
        }
    }

    /**
     * 🔄 Mengalihkan ke Halaman Pembayaran Scalev jika pelanggan bayar ulang.
     */
    public function pay($order_number, ScalevService $scalevService)
    {
        // 1. Cari data order
        $order = Order::where('order_number', $order_number)->firstOrFail();

        // 2. Jika transaksi sudah lunas, langsung arahkan ke halaman pelacakan
        if (in_array($order->payment_status, ['paid', 'settlement'])) {
            return redirect()->route('orders.track', $order->order_number)
                ->with('info', 'Pesanan ini sudah lunas.');
        }

        try {
            // 3. Minta URL Checkout Scalev untuk order ini
            $paymentUrl = $scalevService->createPaymentUrl($order);

            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            return redirect()->route('orders.track', $order->order_number)
                ->with('error', 'Gagal memuat halaman pembayaran Scalev: '.$e->getMessage());
        }
    }

    /**
     * 🔔 Halaman Publik Lacak Pesanan & Riwayat Pre-Order
     * Rute: /pesanan/{order_number?}
     */
    public function track(Request $request, $order_number = null)
    {
        // Ambil input pencarian dari form query string ?search=... ATAU dari parameter URL {order_number}
        $search = trim($request->get('search', $order_number ?? ''));

        // Jika bernilai dummy 'search', bersihkan stringnya
        if ($search === 'search') {
            $search = '';
        }

        $orders = collect();

        // 🔒 Tampilkan pesanan berdasarkan exact match nomor HP atau order_number
        if (! empty($search)) {
            $orders = Order::with(['items', 'payments'])
                ->where('customer_phone', $search)
                ->orWhere('order_number', $search)
                ->latest()
                ->get();
        }

        return view('orders.track', compact('orders', 'search'));
    }
}