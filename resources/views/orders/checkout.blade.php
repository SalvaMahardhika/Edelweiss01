<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pre-Order | Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- 🔒 GOOGLE RECAPTCHA V2 API --}}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .glass { backdrop-filter: blur(20px); background: rgba(255, 255, 255, 0.4); }
        .gold-text { background: linear-gradient(135deg, #e6c89c, #c8a97e, #a67c52); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723] min-h-screen pt-28 pb-12">

@include('layouts.navbar')

<main class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-wide">Konfirmasi Pre-Order</h1>
        <p class="text-[#6b4f4f] text-sm">Selesaikan detail data pengantaran dan skema pembayaran item pesanan Anda.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-2xl text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- TAG FORM UTAMA MEMBUNGKUS KEDUA KOLOM AGAR SEMUA INPUT TERTANGKAP DENGAN AMAN --}}
    <form action="{{ route('checkout.store') }}" method="POST" id="mainCheckoutForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf
        <input type="hidden" name="cart_items" id="cartItemsHiddenField">
        <input type="hidden" name="fulfill_at" id="fulfillAtHiddenField">

        {{-- LEFT COLUMN: DETAIL FORM --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass border border-white/50 rounded-3xl p-6 md:p-8 space-y-6 shadow-xl">
                <h3 class="text-lg font-bold border-b border-[#3e2723]/10 pb-2"><i class="fa-regular fa-address-card mr-2"></i>Informasi Kontak Pelanggan</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Nama Penerima</label>
                        <input type="text" name="customer_name" value="{{ $user->name ?? '' }}" required class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Nomor Telepon / WhatsApp</label>
                        <input type="tel" name="customer_phone" placeholder="Contoh: 08123456789" required class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Alamat Email (Opsional)</label>
                    <input type="email" name="customer_email" value="{{ $user->email ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>

                <h3 class="text-lg font-bold border-b border-[#3e2723]/10 pt-4 pb-2"><i class="fa-solid fa-truck-ramp-box mr-2"></i>Metode & Waktu Pengiriman/Pengambilan</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Metode Pengambilan</label>
                        <select name="order_type" id="orderTypeSelect" onchange="toggleDeliveryAddress(this.value)" class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                            <option value="pickup">Ambil di Toko (Pickup)</option>
                            <option value="delivery">Kirim ke Alamat (Delivery)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Pilih Tanggal</label>
                        <input type="date" id="fulfill_date" required class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                    </div>
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Pilih Jam (Format 24 Jam)</label>
                        <select id="fulfill_hour" required class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                            @for ($i = 0; $i < 24; $i++)
                                @php $hourString = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                                <option value="{{ $hourString }}" {{ $i == 12 ? 'selected' : '' }}>{{ $hourString }} WIB</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- FIELD ALAMAT PENGIRIMAN DINAMIS --}}
                <div id="deliveryAddressContainer" class="hidden transition-all duration-300">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Alamat Lengkap Pengiriman <span class="text-red-500">*</span></label>
                    <textarea name="delivery_address" id="deliveryAddressInput" rows="3" placeholder="Tulis alamat lengkap pengiriman (Nama jalan, nomor rumah, RT/RW, kecamatan, dan kode pos)..." class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition"></textarea>
                </div>

                <h3 class="text-lg font-bold border-b border-[#3e2723]/10 pt-4 pb-2"><i class="fa-solid fa-wallet mr-2"></i> Rencana Skema Pembayaran</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex items-start p-4 bg-white/50 border rounded-2xl cursor-pointer hover:bg-white/80 transition">
                        <input type="radio" name="payment_plan" value="full" checked onchange="calculateOrderSummary()" class="mt-1 accent-[#3e2723]">
                        <span class="ml-3">
                            <span class="block text-sm font-bold text-[#3e2723]">Bayar Lunas (Full Payment)</span>
                            <span class="block text-xs text-gray-500 mt-0.5">Membayar total tagihan secara penuh di awal transaksi.</span>
                        </span>
                    </label>
                    <label class="relative flex items-start p-4 bg-white/50 border rounded-2xl cursor-pointer hover:bg-white/80 transition">
                        <input type="radio" name="payment_plan" value="dp" onchange="calculateOrderSummary()" class="mt-1 accent-[#3e2723]">
                        <span class="ml-3">
                            <span class="block text-sm font-bold text-[#3e2723]">Uang Muka (DP 50%)</span>
                            <span class="block text-xs text-gray-500 mt-0.5">Bayar setengah sekarang, sisanya H-1 waktu pengambilan.</span>
                        </span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Catatan Tambahan / Kustomisasi Kue</label>
                    <textarea name="notes" rows="3" placeholder="Tulis instruksi khusus (tulisan di kue, lilin, varian rasa cadangan dll)..." class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition"></textarea>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: BASKET ITEMS SUMMARY --}}
        <div class="space-y-6">
            <div class="glass border border-white/50 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-lg font-bold border-b border-[#3e2723]/10 pb-2"><i class="fa-solid fa-list-check mr-2"></i>Ringkasan Items</h3>
                
                <div id="checkoutSummaryList" class="space-y-3 max-h-[40vh] overflow-y-auto pr-1">
                    <!-- Dinamis via Javascript -->
                </div>

                <div class="border-t border-[#3e2723]/10 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal Item</span>
                        <span id="summarySubtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Pajak & Layanan</span>
                        <span>Rp 0</span>
                    </div>
                    <div class="flex justify-between font-bold text-base border-t border-dashed pt-2 text-[#3e2723]">
                        <span>Total Keseluruhan</span>
                        <span id="summaryTotal">Rp 0</span>
                    </div>
                    <div id="dpRowSummary" class="flex justify-between font-bold text-emerald-700 bg-emerald-50 p-2.5 rounded-xl hidden">
                        <span>Wajib Bayar Sekarang (DP)</span>
                        <span id="summaryDP">Rp 0</span>
                    </div>
                </div>

                {{-- 🔒 RECAPTCHA V2 DI BAWAH TOTAL HARGA --}}
                <div class="pt-2 flex flex-col items-center justify-center space-y-1">
                    <div class="g-recaptcha" data-sitekey="{{ env('NOCAPTCHA_SITEKEY', '6Ld_dummy_site_key_here') }}" data-callback="recaptchaSuccessCallback"></div>
                    <p id="captchaErrorText" class="text-xs text-red-600 font-semibold hidden">Silakan centang reCAPTCHA terlebih dahulu!</p>
                </div>

                <button type="button" onclick="triggerFormSubmit()" class="w-full py-4 bg-[#3e2723] text-white font-bold rounded-2xl shadow-xl hover:bg-[#2c1b18] transition duration-300 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-shield-heart"></i> Proses Pre-Order Sekarang
                </button>
            </div>
        </div>
    </form>
</main>

@include('layouts.footer')

<script>
    let checkoutCart = [];

    if (sessionStorage.getItem('bakery_cart')) {
        checkoutCart = JSON.parse(sessionStorage.getItem('bakery_cart'));
    }

    if (checkoutCart.length === 0) {
        window.location.href = "{{ route('menu') }}";
    }

    // Callback saat reCAPTCHA dicentang
    function recaptchaSuccessCallback() {
        document.getElementById('captchaErrorText').classList.add('hidden');
    }

    // PERBAIKAN: Jangan pernah hapus addressInput.value saat toggle agar data alamat pengiriman aman!
    function toggleDeliveryAddress(orderType) {
        const addressContainer = document.getElementById('deliveryAddressContainer');
        const addressInput = document.getElementById('deliveryAddressInput');

        if (orderType === 'delivery') {
            addressContainer.classList.remove('hidden');
            addressInput.required = true;
            addressInput.disabled = false;
        } else {
            addressContainer.classList.add('hidden');
            addressInput.required = false;
            addressInput.disabled = true; // Disabled dipasang hanya saat pickup agar tidak terkirim
        }
    }

    function triggerFormSubmit() {
        const form = document.getElementById('mainCheckoutForm');
        const dateVal = document.getElementById('fulfill_date').value;
        const hourVal = document.getElementById('fulfill_hour').value;
        const hiddenField = document.getElementById('fulfillAtHiddenField');
        const captchaError = document.getElementById('captchaErrorText');

        // 1. Verifikasi reCAPTCHA
        const captchaResponse = typeof grecaptcha !== 'undefined' ? grecaptcha.getResponse() : '';
        if (!captchaResponse) {
            captchaError.classList.remove('hidden');
            return;
        }
        captchaError.classList.add('hidden');

        // 2. Gabungkan tanggal & jam
        if (dateVal && hourVal) {
            hiddenField.value = `${dateVal} ${hourVal}:00`;
        }

        // 3. Submit form jika validasi HTML terpenuhi
        if (form.reportValidity()) {
            form.submit();
        }
    }

    function renderCheckoutSummary() {
        const container = document.getElementById('checkoutSummaryList');
        const hiddenField = document.getElementById('cartItemsHiddenField');
        
        container.innerHTML = '';
        hiddenField.value = JSON.stringify(checkoutCart);

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fulfill_date').setAttribute('min', today);

        checkoutCart.forEach(item => {
            const el = document.createElement('div');
            el.className = "flex items-center gap-3 p-2 bg-white/30 border border-white/40 rounded-xl";
            el.innerHTML = `
                <img src="${item.image ? item.image : '/img/logo/logo2.png'}" class="w-10 h-10 object-cover rounded-lg bg-gray-100 shrink-0">
                <div class="flex-1 min-w-0 text-xs">
                    <p class="font-bold text-[#3e2723] truncate">${item.name}</p>
                    <p class="text-gray-500 mt-0.5">${item.quantity} x Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</p>
                </div>
                <span class="text-xs font-bold text-[#3e2723]">Rp ${new Intl.NumberFormat('id-ID').format(item.price * item.quantity)}</span>
            `;
            container.appendChild(el);
        });

        calculateOrderSummary();
        
        const currentOrderType = document.getElementById('orderTypeSelect').value;
        toggleDeliveryAddress(currentOrderType);
    }

    function calculateOrderSummary() {
        let subtotal = 0;
        checkoutCart.forEach(item => {
            subtotal += item.price * item.quantity;
        });

        document.getElementById('summarySubtotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        document.getElementById('summaryTotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);

        const selectedPlan = document.querySelector('input[name="payment_plan"]:checked').value;
        const dpRow = document.getElementById('dpRowSummary');

        if (selectedPlan === 'dp') {
            const dpCalculation = subtotal * 0.5;
            document.getElementById('summaryDP').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(dpCalculation);
            dpRow.classList.remove('hidden');
        } else {
            dpRow.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', renderCheckoutSummary);
</script>
</body>
</html>