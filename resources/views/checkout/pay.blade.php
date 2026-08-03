<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pre-Order | Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- 🔑 DOKU CHECKOUT JS SDK (AUTO-SWITCH SANDBOX / PRODUCTION) --}}
    @if(config('services.doku.is_production', env('DOKU_IS_PRODUCTION', false)))
        <script src="https://jokul.doku.com/jokul-checkout-js/v1/jokul-checkout-1.0.0.js"></script>
    @else
        <script src="https://sandbox.doku.com/jokul-checkout-js/v1/jokul-checkout-1.0.0.js"></script>
    @endif

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .glass { backdrop-filter: blur(20px); background: rgba(255, 255, 255, 0.4); }
        .gold-text { background: linear-gradient(135deg, #e6c89c, #c8a97e, #a67c52); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723] min-h-screen pt-28 pb-12">

@include('layouts.navbar')

<main class="max-w-md mx-auto px-4">
    <div class="glass border border-white/50 rounded-3xl p-6 md:p-8 shadow-2xl text-center space-y-6">
        
        {{-- ICON STATUS --}}
        <div class="w-20 h-20 bg-[#3e2723]/10 text-[#3e2723] rounded-full flex items-center justify-center text-3xl mx-auto animate-pulse">
            <i class="fa-solid fa-wallet"></i>
        </div>

        {{-- DETAIL PESANAN --}}
        <div>
            <h1 class="text-2xl font-bold tracking-wide">Menunggu Pembayaran</h1>
            <p class="text-sm text-gray-500 mt-1">Nomor Pesanan: <span class="font-semibold text-[#3e2723]">{{ $order->order_number }}</span></p>
        </div>

        <div class="bg-white/50 border border-white/80 rounded-2xl p-4 text-sm space-y-2">
            <div class="flex justify-between text-gray-600">
                <span>Nama Pelanggan</span>
                <span class="font-medium text-[#3e2723]">{{ $order->customer_name }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Skema Pembayaran</span>
                <span class="font-medium text-amber-800 uppercase bg-amber-50 px-2 py-0.5 rounded-md text-xs">
                    {{ $order->payment_plan == 'dp' ? 'Uang Muka (DP 50%)' : 'Bayar Lunas' }}
                </span>
            </div>
            <div class="border-t border-dashed my-2 pt-2 flex justify-between font-bold text-base text-[#3e2723]">
                <span>Jumlah yang Harus Dibayar</span>
                <span class="gold-text">
                    @php
                        $amountToPay = ($order->payment_plan == 'dp' && $order->amount_paid == 0) 
                            ? $order->dp_amount 
                            : ($order->total_amount - $order->amount_paid);
                    @endphp

                    Rp {{ number_format($amountToPay, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- BUTTON ACTIONS --}}
        <div class="space-y-3 pt-2">
            <button id="pay-button" class="w-full py-4 bg-[#3e2723] hover:bg-[#2c1b18] text-white font-bold rounded-2xl shadow-xl transition duration-300 flex items-center justify-center gap-2 text-base">
                <i class="fa-solid fa-credit-card"></i> Bayar Sekarang
            </button>
            
            <a href="{{ route('menu') }}" class="block w-full py-3.5 bg-white/40 hover:bg-white/70 text-[#3e2723] font-semibold rounded-2xl border border-white/60 shadow-sm transition duration-300 text-sm">
                Kembali ke Katalog Menu
            </a>
        </div>

        <p class="text-[10px] text-gray-400">Pre-Order Anda aman terenkripsi melewati sistem pembayaran resmi DOKU Payment Gateway.</p>
    </div>
</main>

@include('layouts.footer')

{{-- 🔑 SCRIPTS INTEGRASI DOKU CHECKOUT --}}
<script type="text/javascript">
    const payButton = document.getElementById('pay-button');
    // Menerima $paymentUrl dari controller (atau fallback ke $order->snap_token)
    const paymentUrl = "{{ $paymentUrl ?? ($order->snap_token ?? '') }}";

    // Fungsi pembuka Pop-Up DOKU Checkout Layer
    function openDokuCheckout() {
        if (!paymentUrl) {
            alert("URL Pembayaran tidak ditemukan. Silakan muat ulang halaman.");
            return;
        }

        // Hapus keranjang lokal pengguna karena order sudah tercatat di sistem
        sessionStorage.removeItem('bakery_cart');

        // Panggil fungsi modal pop-up resmi milik DOKU JS
        if (typeof loadJokulCheckout === 'function') {
            loadJokulCheckout(paymentUrl);
        } else {
            // Fallback jika SDK gagal dimuat, redirect langsung ke halaman pembayaran DOKU
            window.location.href = paymentUrl;
        }
    }

    // Picu trigger pop-up otomatis saat pertama kali halaman termuat penuh
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(openDokuCheckout, 800);
    });

    // Event handler klik tombol "Bayar Sekarang"
    payButton.addEventListener('click', function (e) {
        e.preventDefault();
        openDokuCheckout();
    });
</script>
</body>
</html>