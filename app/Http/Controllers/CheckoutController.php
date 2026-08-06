<?php

namespace App\Http\Controllers;

use App\Models\DisabledDate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Models\User;
use App\Services\DokuService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Tampilkan halaman formulir checkout.
     * Bisa diakses oleh User yang sudah login maupun Guest.
     */
    public function index()
    {
        // Ambil data user jika terautentikasi (null jika guest)
        $user = Auth::user();

        // ðŸ”´ FIX: Memformat tanggal secara eksplisit menjadi string 'YYYY-MM-DD'
        // agar terhindar dari casting ISO Carbon string ("YYYY-MM-DDT00:00:00Z") di JavaScript JSON
        $disabledDates = DisabledDate::where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('Y-m-d'),
                    'reason' => $item->reason ?? 'Kuota Penuh / Toko Libur',
                ];
            });

        return view('orders.checkout', compact('user', 'disabledDates'));
    }

    /**
     * Proses simpan order baru ke database (Support User & Guest Checkout).
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255', // Wajib diisi agar data tracing & account linking bekerja
            'order_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:order_type,delivery|nullable|string',
            'payment_method' => 'nullable|in:payment_gateway,manual_wa', // ðŸŸ¢ Validasi metode pembayaran
            'payment_plan' => 'required|in:full,dp',
            // Waktu kesiapan minimal 2 jam ke depan (now + 2 hours)
            'fulfill_at' => 'required|date|after_or_equal:'.now()->addHours(2)->format('Y-m-d H:i:s'),
            'notes' => 'nullable|string',
            'cart_items' => 'required|json',
        ], [
            'customer_email.required' => 'Alamat email wajib diisi untuk pengiriman bukti & tracking pesanan.',
            'fulfill_at.after_or_equal' => 'Waktu kesiapan pesanan minimal 2 jam dari sekarang.',
        ]);

        // ðŸ”’ 0. VALIDASI PENGECEKAN TANGGAL TERBLOKIR / LOCK TANGGAL (BACKEND SECURITY)
        $fulfillDateOnly = null;
        if ($request->filled('fulfill_at')) {
            $fulfillDateOnly = Carbon::parse($request->fulfill_at)->format('Y-m-d');
        }

        if ($fulfillDateOnly && DisabledDate::where('date', $fulfillDateOnly)->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['fulfill_at' => 'Maaf, kuota pesanan untuk tanggal '.Carbon::parse($fulfillDateOnly)->translatedFormat('d F Y').' sudah PENUH / Toko Libur. Silakan pilih tanggal lain.']);
        }

        $cartData = json_decode($request->cart_items, true);
        if (empty($cartData)) {
            return redirect()->back()->withErrors(['cart_items' => 'Keranjang Anda kosong.']);
        }

        try {
            DB::beginTransaction();

            // 1. Dapatkan atau Buat Record User (Guest-to-Account Linking Strategy)
            if (Auth::check()) {
                $userId = Auth::id();
            } else {
                // Cari user berdasarkan email atau no. hp, jika tidak ada -> buat akun guest baru
                $user = User::where('email', $request->customer_email)
                    ->orWhere(function ($query) use ($request) {
                        $query->whereNotNull('phone')->where('phone', $request->customer_phone);
                    })
                    ->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $request->customer_name,
                        'email' => $request->customer_email,
                        'phone' => $request->customer_phone,
                        'password' => Hash::make(Str::random(16)), // Password acak sementara
                        'role' => 'customer',
                        'status' => true,
                        'is_guest' => true, // Flag akun guest
                    ]);
                }

                $userId = $user->id;
            }

            // 2. Hitung finansial order berdasarkan data database asli (aman dari manipulasi client-side)
            $subtotal = 0;
            $itemsToSave = [];

            foreach ($cartData as $item) {
                // Validasi produk ada di database
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

            // 3. Hitung Pajak/PPN 11% secara presisi
            $taxAmount = bcmul((string) $subtotal, '0.11', 2);
            $totalAmount = bcadd((string) $subtotal, (string) $taxAmount, 2);

            // Atur skema uang muka (DP 50%)
            $dpAmount = ($request->payment_plan === 'dp') ? ($totalAmount * 0.5) : 0;

            // 4. Generate Nomor Order Unik (cth: EDL-20260717-0001)
            $dateString = now()->format('Ymd');
            $latestOrder = Order::where('order_number', 'LIKE', "EDL-{$dateString}-%")->latest()->first();
            $nextSequence = $latestOrder ? ((int) Str::afterLast($latestOrder->order_number, '-') + 1) : 1;
            $orderNumber = "EDL-{$dateString}-".str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            // Ambil metode pembayaran (Default ke 'payment_gateway' jika kosong)
            $paymentMethod = $request->input('payment_method', 'payment_gateway');

            // 5. Simpan ke tabel orders (user_id & payment_method terisi secara konsisten)
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'order_type' => $request->order_type,
                'delivery_address' => $request->delivery_address,
                'status' => 'pending',
                'payment_method' => $paymentMethod, // ðŸŸ¢ SIMPAN NILAI PAYMENT METHOD
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

            // 6. Simpan ke tabel order_items
            foreach ($itemsToSave as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            DB::commit();

            // ðŸ”” 7. KIRIM NOTIFIKASI REAL-TIME KE TELEGRAM GRUP PESANAN
            try {
                $telegramService = new TelegramService;
                $telegramService->sendOrderNotification($order->load('items'));
            } catch (\Exception $telegramEx) {
                // Silently log error telegram agar transaksi user tidak terganggu meski bot gagal kirim
                Log::error('Telegram notification error: '.$telegramEx->getMessage());
            }

            // ðŸŸ¢ 8. PEMBERCABANGAN REDIRECT
            // Jika memilih Manual WA, langsung arahkan ke halaman Lacak Pesanan / Detail Pesanan
            if ($paymentMethod === 'manual_wa') {
                return redirect()->to('/pesanan/'.$order->order_number)
                    ->with('success', 'Pesanan berhasil dibuat! Silakan lanjutkan konfirmasi pembayaran via WhatsApp.');
            }

            // Jika Payment Gateway (DOKU), redirect ke halaman bayar
            return redirect()->route('checkout.pay', $order->order_number);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal memproses Pre-Order: '.$e->getMessage()]);
        }
    }

    /**
     * Tampilkan halaman pembayaran DOKU (Awal / Pembuatan Pesanan).
     */
    public function pay($order_number, DokuService $dokuService)
    {
        // 1. Cari data order
        $order = Order::where('order_number', $order_number)->firstOrFail();

        // 2. Jika order SUDAH MEMILIKI snap_token (paymentUrl DOKU), pakai URL tersebut
        if ($order->snap_token) {
            $paymentUrl = $order->snap_token;
        } else {
            // Jika BELUM PUNYA (transaksi baru), minta ke DOKU lalu simpan ke DB
            $paymentUrl = $dokuService->getPaymentUrl($order, 'initial');

            $order->update([
                'snap_token' => $paymentUrl,
            ]);
        }

        // 3. Tampilkan halaman pembayaran
        return view('checkout.pay', compact('order', 'paymentUrl'));
    }

    /**
     * Halaman Redirect setelah transaksi DOKU selesai
     */
    public function success($orderNumber)
    {
        // Cari pesanan berdasarkan order_number
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // [AUTO-UPDATE STATUS] Update status pembayaran pesanan jika masih pending
        if ($order->payment_status === 'pending' || $order->payment_status === 'unpaid') {
            $paymentPlan = is_object($order->payment_plan) ? $order->payment_plan->value : $order->payment_plan;

            if (strtolower($paymentPlan) === 'dp') {
                $order->update([
                    'payment_status' => 'dp',
                    'amount_paid' => $order->dp_amount ?? ($order->total_amount / 2),
                    'status' => 'confirmed', // Atau status produksi awal
                ]);
            } else {
                $order->update([
                    'payment_status' => 'paid', // atau 'lunas'
                    'amount_paid' => $order->total_amount,
                    'status' => 'confirmed',
                ]);
            }

            // Kirim notifikasi pesanan ke Telegram jika TelegramService tersedia
            try {
                if (class_exists(TelegramService::class)) {
                    app(TelegramService::class)->sendOrderNotification($order);
                }
            } catch (\Exception $e) {
                Log::error('Telegram Notif Error: '.$e->getMessage());
            }
        }

        // Tampilkan halaman sukses / detail pesanan via URL /pesanan/{order_number}
        return redirect()->to('/pesanan/'.$order->order_number)
            ->with('success', 'Pembayaran berhasil dikonfirmasi! Pesanan Anda sedang diproses.');
    }

    /**
     * ðŸ”” Halaman Publik Lacak Pesanan & Riwayat Pre-Order
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

        // ðŸ”’ HANYA TAMPILKAN PESANAN JIKA USER SUDAH MEMASUKKAN NOMOR HP / EMAIL / ORDER LENGKAP
        if (! empty($search)) {
            $orders = Order::with(['items', 'payments'])
                ->where('customer_phone', $search)  // Match nomor HP
                ->orWhere('customer_email', $search) // Match email
                ->orWhere('order_number', $search)   // Match nomor order
                ->latest()
                ->get();
        }

        return view('orders.track', compact('orders', 'search'));
    }
}
