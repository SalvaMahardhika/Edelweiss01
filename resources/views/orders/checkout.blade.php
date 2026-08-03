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

    {{-- TAG FORM UTAMA MEMBUNGKUS KEDUA KOLOM --}}
    <form action="{{ route('checkout.store') }}" method="POST" id="mainCheckoutForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf
        <input type="hidden" name="cart_items" id="cartItemsHiddenField">
        <input type="hidden" name="fulfill_at" id="fulfillAtHiddenField">
        <input type="hidden" name="payment_method" id="paymentMethodHiddenField" value="payment_gateway">

        {{-- LEFT COLUMN: DETAIL FORM --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass border border-white/50 rounded-3xl p-6 md:p-8 space-y-6 shadow-xl">
                
                {{-- HEADER INFORMASI KONTAK & STATUS AUTH/GUEST --}}
                <div class="flex items-center justify-between border-b border-[#3e2723]/10 pb-2">
                    <h3 class="text-lg font-bold"><i class="fa-regular fa-address-card mr-2"></i>Informasi Kontak Pelanggan</h3>
                    @auth
                        <span class="text-xs bg-emerald-100 text-emerald-800 font-semibold px-3 py-1 rounded-full border border-emerald-300">
                            <i class="fa-solid fa-user-check mr-1"></i> {{ auth()->user()->name }}
                        </span>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-[#a67c52] hover:underline">
                            Sudah punya akun? Login di sini <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    @endauth
                </div>

                {{-- NOTIFIKASI KHUSUS GUEST --}}
                @guest
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-amber-600 text-sm"></i>
                        <span>Anda memesan sebagai <strong>Guest</strong>. Email & Nomor WA aktif wajib diisi untuk bukti pembayaran & tracking pesanan.</span>
                    </div>
                @endguest
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Nama Penerima <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', $user->name ?? '') }}" required placeholder="Nama lengkap penerima" class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                        <input type="tel" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', $user->phone ?? '') }}" placeholder="Contoh: 08123456789" required class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">
                        Alamat Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="customer_email" 
                           id="customer_email"
                           value="{{ old('customer_email', $user->email ?? '') }}" 
                           placeholder="Contoh: nama@email.com"
                           @auth readonly @else required @endauth 
                           class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition @auth bg-gray-100/70 cursor-not-allowed @endauth">
                    @guest
                        <p class="text-[11px] text-gray-500 mt-1">Email ini akan digunakan jika nanti Anda mendaftar akun untuk melihat riwayat pesanan.</p>
                    @endguest
                </div>

                <h3 class="text-lg font-bold border-b border-[#3e2723]/10 pt-4 pb-2"><i class="fa-solid fa-truck-ramp-box mr-2"></i>Metode & Waktu Pengiriman/Pengambilan</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Metode Pengambilan</label>
                        <select name="order_type" id="orderTypeSelect" onchange="toggleDeliveryAddress(this.value)" class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                            <option value="pickup" {{ old('order_type') == 'pickup' ? 'selected' : '' }}>Ambil di Toko (Pickup)</option>
                            <option value="delivery" {{ old('order_type') == 'delivery' ? 'selected' : '' }}>Kirim ke Alamat (Delivery)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Pilih Tanggal</label>
                        <input type="date" id="fulfill_date" onchange="validateDisabledDate(this)" oninput="validateDisabledDate(this)" required class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                        <p id="disabledDateErrorText" class="text-xs text-red-600 font-semibold mt-1 hidden">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i> Tanggal ini penuh / toko libur!
                        </p>
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
                    <textarea name="delivery_address" id="deliveryAddressInput" rows="3" placeholder="Tulis alamat lengkap pengiriman (Nama jalan, nomor rumah, RT/RW, kecamatan, dan kode pos)..." class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">{{ old('delivery_address') }}</textarea>
                </div>

                <h3 class="text-lg font-bold border-b border-[#3e2723]/10 pt-4 pb-2"><i class="fa-solid fa-wallet mr-2"></i> Rencana Skema Pembayaran</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex items-start p-4 bg-white/50 border rounded-2xl cursor-pointer hover:bg-white/80 transition">
                        <input type="radio" name="payment_plan" value="full" {{ old('payment_plan', 'full') === 'full' ? 'checked' : '' }} onchange="calculateOrderSummary()" class="mt-1 accent-[#3e2723]">
                        <span class="ml-3">
                            <span class="block text-sm font-bold text-[#3e2723]">Bayar Lunas (Full Payment)</span>
                            <span class="block text-xs text-gray-500 mt-0.5">Membayar total tagihan secara penuh di awal transaksi.</span>
                        </span>
                    </label>
                    <label class="relative flex items-start p-4 bg-white/50 border rounded-2xl cursor-pointer hover:bg-white/80 transition">
                        <input type="radio" name="payment_plan" value="dp" {{ old('payment_plan') === 'dp' ? 'checked' : '' }} onchange="calculateOrderSummary()" class="mt-1 accent-[#3e2723]">
                        <span class="ml-3">
                            <span class="block text-sm font-bold text-[#3e2723]">Uang Muka (DP 50%)</span>
                            <span class="block text-xs text-gray-500 mt-0.5">Bayar setengah sekarang, sisanya H-1 waktu pengambilan.</span>
                        </span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">Catatan Tambahan / Kustomisasi Kue</label>
                    <textarea name="notes" id="notesInput" rows="3" placeholder="Tulis instruksi khusus (tulisan di kue, lilin, varian rasa cadangan dll)..." class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">{{ old('notes') }}</textarea>
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
                        <span>Pajak & Layanan (10%)</span>
                        <span id="summaryTax">Rp 0</span>
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

                {{-- 📜 CHECKBOX SYARAT & KETENTUAN --}}
                <div class="pt-2 border-t border-dashed border-[#3e2723]/20">
                    <label class="flex items-start gap-2.5 cursor-pointer text-xs text-gray-700 select-none">
                        <input type="checkbox" id="termsCheckbox" onchange="toggleTermsError()" class="mt-0.5 w-4 h-4 accent-[#3e2723] rounded">
                        <span>
                            Saya telah membaca dan menyetujui 
                            <button type="button" onclick="openTermsModal()" class="font-bold text-[#a67c52] underline hover:text-[#3e2723]">
                                Syarat & Ketentuan Pemesanan
                            </button> 
                            Edelweiss Bakery. <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <p id="termsErrorText" class="text-xs text-red-600 font-semibold mt-1 hidden">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Anda wajib menyetujui Syarat & Ketentuan terlebih dahulu!
                    </p>
                </div>

                {{-- 🔒 RECAPTCHA V2 DI BAWAH TOTAL HARGA --}}
                <div class="pt-2 flex flex-col items-center justify-center space-y-1">
                    <div class="g-recaptcha" data-sitekey="{{ env('NOCAPTCHA_SITEKEY', '6Ld_dummy_site_key_here') }}" data-callback="recaptchaSuccessCallback"></div>
                    <p id="captchaErrorText" class="text-xs text-red-600 font-semibold hidden">Silakan centang reCAPTCHA terlebih dahulu!</p>
                </div>

                <button type="button" onclick="triggerFormSubmit()" class="w-full py-4 bg-[#3e2723] text-white font-bold rounded-2xl shadow-xl hover:bg-[#2c1b18] transition duration-300 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-shield-heart"></i> Lanjut ke Pembayaran
                </button>
            </div>
        </div>
    </form>
</main>

{{-- 💳 MODAL POPUP METODE PEMBAYARAN --}}
<div id="paymentModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-md p-6 rounded-[2.5rem] bg-white/80 backdrop-blur-2xl border border-white/80 shadow-2xl space-y-6 text-center my-auto">
        
        <div>
            <div class="w-16 h-16 rounded-3xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <h3 class="text-xl font-black text-[#3e2723]">Pilih Metode Pembayaran</h3>
            <p class="text-xs font-medium text-gray-600 mt-1">Pilih alur pembayaran yang paling nyaman untuk Anda.</p>
        </div>

        <div class="space-y-3">
            {{-- Pilihan 1: Payment Gateway --}}
            <button type="button" onclick="submitViaPG()" class="w-full p-4 rounded-2xl bg-white/70 border border-white hover:border-[#3e2723] hover:bg-white text-left transition shadow-sm group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center text-lg group-hover:scale-110 transition">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#3e2723]">Payment Gateway (Otomatis)</p>
                            <p class="text-[10px] text-gray-500">QRIS, Virtual Account, & E-Wallet (DOKU)</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-gray-400 group-hover:translate-x-1 transition"></i>
                </div>
            </button>

            {{-- Pilihan 2: Direct WhatsApp --}}
            <button type="button" onclick="submitViaWhatsApp()" class="w-full p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 hover:border-emerald-500 hover:bg-emerald-100 text-left transition shadow-sm group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg group-hover:scale-110 transition">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-emerald-950">Transfer Manual via WhatsApp</p>
                            <p class="text-[10px] text-emerald-700">Kirim detail & konfirmasi bayar via Chat WA Admin</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-emerald-500 group-hover:translate-x-1 transition"></i>
                </div>
            </button>
        </div>

        <button type="button" onclick="closePaymentModal()" class="w-full py-2.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition">
            Batal & Kembali
        </button>
    </div>
</div>

{{-- 📜 MODAL POPUP SYARAT & KETENTUAN --}}
<div id="termsModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2.5rem] shadow-2xl p-6 md:p-8 max-h-[85vh] flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-[#3e2723]/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-lg">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#3e2723]">Syarat & Ketentuan Pemesanan</h3>
                </div>
                <button type="button" onclick="closeTermsModal()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">✕</button>
            </div>

            <div class="overflow-y-auto max-h-[55vh] mt-4 pr-2 space-y-4 text-xs md:text-sm text-[#4a3525] leading-relaxed">
                <p class="font-medium text-gray-600">
                    Dengan melakukan pemesanan (Pre-Order) di <strong>Edelweiss Bakery</strong>, Anda dianggap telah membaca, memahami, dan menyetujui seluruh ketentuan di bawah ini:
                </p>

                <div class="space-y-1">
                    <h4 class="font-bold text-[#3e2723] uppercase tracking-wider text-xs">1. Ketentuan Pemesanan (Pre-Order)</h4>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        <li>Seluruh produk dibuat secara fresh berdasarkan pemesanan (<em>made-to-order</em>).</li>
                        <li>Pemesan wajib memastikan detail produk, jumlah pesanan, tanggal, dan jam pengambilan/pengiriman yang dipilih sudah benar sebelum transaksi.</li>
                    </ul>
                </div>

                <div class="space-y-1">
                    <h4 class="font-bold text-[#3e2723] uppercase tracking-wider text-xs">2. Skema Pembayaran</h4>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        <li><strong>Bayar Lunas (Full Payment):</strong> Pembayaran secara penuh di awal transaksi.</li>
                        <li><strong>Uang Muka (DP 50%):</strong> Pembayaran DP minimal 50% dilakukan saat pemesanan. Sisa pelunasan dibayarkan offline/tunai pada saat pengambilan atau penerimaan barang.</li>
                    </ul>
                </div>

                <div class="space-y-1">
                    <h4 class="font-bold text-[#3e2723] uppercase tracking-wider text-xs">3. Batas Waktu & Pembatalan Otomatis (Auto-Cancel)</h4>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        <li>Pesanan yang belum dibayar dalam waktu <strong>1x24 jam</strong> akan secara otomatis dibatalkan oleh sistem.</li>
                    </ul>
                </div>

                <div class="space-y-1">
                    <h4 class="font-bold text-[#3e2723] uppercase tracking-wider text-xs">4. Pembatalan & Pengembalian Dana (Refund)</h4>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        <li>Pembatalan sepihak oleh pelanggan kurang dari <strong>H-1 waktu pengambilan/pengiriman</strong> menyebabkan dana pembayaran (DP/Lunas) tidak dapat dikembalikan.</li>
                        <li>Apabila terjadi kendala produksi dari pihak Edelweiss Bakery, dana dikembalikan penuh (100%).</li>
                    </ul>
                </div>

                <div class="space-y-1">
                    <h4 class="font-bold text-[#3e2723] uppercase tracking-wider text-xs">5. Pengambilan & Pengiriman Produk</h4>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        <li><strong>Ambil di Toko (Pickup):</strong> Pengambilan sesuai tanggal & jam operasional toko yang disepakati.</li>
                        <li><strong>Pengiriman Alamat (Delivery):</strong> Seluruh pengiriman ditangani langsung oleh <strong> Internal Edelweiss Bakery</strong> untuk menjamin keamanan, kebersihan, dan kondisi produk tetap sempurna hingga tiba di lokasi Anda.</li>
                        <li>Pelanggan wajib memastikan alamat lengkap & nomor telepon/WA penerima aktif saat jadwal pengiriman.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-[#3e2723]/10">
            <button type="button" onclick="acceptTermsFromModal()" class="w-full py-3.5 bg-[#3e2723] text-white font-bold rounded-2xl shadow-lg hover:bg-[#2c1b18] transition text-center">
                Saya Mengerti & Setuju
            </button>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
    let checkoutCart = [];
    const TAX_RATE = 0.10; // Persentase Pajak PPN (10%)
    const ADMIN_WA = '6287794082895'; // Nomor WA Admin Edelweiss Bakery
    
    // 🔒 DATA TANGGAL TERBLOKIR DARI ADMIN
    const disabledDates = @json($disabledDates ?? []);

    if (sessionStorage.getItem('bakery_cart')) {
        checkoutCart = JSON.parse(sessionStorage.getItem('bakery_cart'));
    }

    if (checkoutCart.length === 0) {
        window.location.href = "{{ route('menu') }}";
    }

    // 🔒 HELPER CEK TANGGAL TERBLOKIR
    function isDateDisabled(dateString) {
        if (!dateString) return false;
        const formattedDate = dateString.split(' ')[0];
        return disabledDates.includes(formattedDate);
    }

    // 🔒 VALIDASI PENGECEKAN TANGGAL TERBLOKIR REAL-TIME
    function validateDisabledDate(input) {
        const selectedDate = input.value;
        const errorText = document.getElementById('disabledDateErrorText');

        if (isDateDisabled(selectedDate)) {
            errorText.classList.remove('hidden');
            alert('Maaf, kuota pemesanan untuk tanggal tersebut sudah PENUH atau toko sedang LIBUR. Silakan pilih tanggal lain!');
            input.value = ''; // Kosongkan pilihan tanggal
        } else {
            errorText.classList.add('hidden');
        }
    }

    // Callback reCAPTCHA
    function recaptchaSuccessCallback() {
        document.getElementById('captchaErrorText').classList.add('hidden');
    }

    // 📜 MODAL & CHECKBOX SYARAT & KETENTUAN LOGIC
    function openTermsModal() {
        document.getElementById('termsModal').classList.remove('hidden');
    }

    function closeTermsModal() {
        document.getElementById('termsModal').classList.add('hidden');
    }

    function acceptTermsFromModal() {
        document.getElementById('termsCheckbox').checked = true;
        toggleTermsError();
        closeTermsModal();
    }

    function toggleTermsError() {
        const checkbox = document.getElementById('termsCheckbox');
        const errorText = document.getElementById('termsErrorText');
        if (checkbox.checked) {
            errorText.classList.add('hidden');
        }
    }

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
            addressInput.disabled = true;
        }
    }

    // Pemicu Klik "Lanjut ke Pembayaran"
    function triggerFormSubmit() {
        const form = document.getElementById('mainCheckoutForm');
        const dateInput = document.getElementById('fulfill_date');
        const dateVal = dateInput.value;
        const hourVal = document.getElementById('fulfill_hour').value;
        const hiddenField = document.getElementById('fulfillAtHiddenField');
        const captchaError = document.getElementById('captchaErrorText');
        const termsCheckbox = document.getElementById('termsCheckbox');
        const termsError = document.getElementById('termsErrorText');
        const errorText = document.getElementById('disabledDateErrorText');

        // 0. Cek kembali jika tanggal yang dipilih termasuk tanggal terblokir
        if (isDateDisabled(dateVal)) {
            errorText.classList.remove('hidden');
            alert('Maaf, tanggal yang Anda pilih sedang tidak menerima pesanan. Silakan ganti tanggal.');
            dateInput.value = '';
            dateInput.focus();
            return;
        }

        // 1. Cek Checkbox Syarat & Ketentuan
        if (!termsCheckbox.checked) {
            termsError.classList.remove('hidden');
            termsCheckbox.focus();
            return;
        }
        termsError.classList.add('hidden');

        // 2. Cek reCAPTCHA
        const captchaResponse = typeof grecaptcha !== 'undefined' ? grecaptcha.getResponse() : '';
        if (!captchaResponse) {
            captchaError.classList.remove('hidden');
            return;
        }
        captchaError.classList.add('hidden');

        // 3. Gabungkan tanggal & jam
        if (dateVal && hourVal) {
            hiddenField.value = `${dateVal} ${hourVal}:00`;
        }

        // 4. Jika Form Valid, Buka Modal Pemilihan Metode Pembayaran
        if (form.reportValidity()) {
            document.getElementById('paymentModal').classList.remove('hidden');
        }
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    // Submit Opsi 1: Payment Gateway
    function submitViaPG() {
        const dateInput = document.getElementById('fulfill_date');
        const dateVal = dateInput.value;

        // 🚨 PROTEKSI KETAT SEBELUM SUBMIT PAYMENT GATEWAY
        if (isDateDisabled(dateVal)) {
            alert('Maaf, tanggal yang Anda pilih sudah PENUH/LIBUR. Silakan ganti tanggal.');
            closePaymentModal();
            dateInput.value = '';
            dateInput.focus();
            return;
        }

        document.getElementById('paymentMethodHiddenField').value = 'payment_gateway';
        document.getElementById('mainCheckoutForm').submit();
    }

    // 🟢 SCRIPT WHATSAPP BERSIH TANPA EMOJI (MENCEGAH KARAKTER TANDA TANYA / CORRUPT)
    function submitViaWhatsApp() {
        const dateInput = document.getElementById('fulfill_date');
        const dateVal = dateInput.value;

        // 🚨 PROTEKSI KETAT SEBELUM PROSES WA & SUBMIT MANUAL
        if (isDateDisabled(dateVal)) {
            alert('Maaf, kuota pemesanan untuk tanggal tersebut sudah PENUH atau toko sedang LIBUR. Pesanan tidak dapat dilanjutkan.');
            closePaymentModal();
            dateInput.value = '';
            dateInput.focus();
            return;
        }

        document.getElementById('paymentMethodHiddenField').value = 'manual_wa';

        // 1. Ambil Data Form untuk Template WhatsApp
        const name = document.getElementById('customer_name').value;
        const phone = document.getElementById('customer_phone').value;
        const email = document.getElementById('customer_email').value;
        const orderType = document.getElementById('orderTypeSelect').value;
        const address = document.getElementById('deliveryAddressInput').value;
        const hourVal = document.getElementById('fulfill_hour').value;
        const notes = document.getElementById('notesInput').value;
        const paymentPlan = document.querySelector('input[name="payment_plan"]:checked').value;

        // Hitung Total Finansial
        let subtotal = 0;
        let itemLines = '';
        checkoutCart.forEach(item => {
            subtotal += item.price * item.quantity;
            itemLines += `- ${item.quantity}x ${item.name} (Rp ${new Intl.NumberFormat('id-ID').format(item.price * item.quantity)})\n`;
        });

        const taxAmount = Math.round(subtotal * TAX_RATE);
        const grandTotal = subtotal + taxAmount;
        const payNow = paymentPlan === 'dp' ? Math.round(grandTotal * 0.5) : grandTotal;

        // Format Pesan WhatsApp Standar Polos (Tanpa Emoji)
        let waMessage = `Halo Admin Edelweiss Bakery!\n`;
        waMessage += `Saya ingin memesan kue/roti secara *TRANSFER MANUAL*.\n\n`;
        
        waMessage += `INFORMASI PELANGGAN:\n`;
        waMessage += `- Nama: *${name}*\n`;
        waMessage += `- No. Telp/WA: ${phone}\n`;
        waMessage += `- Email: ${email}\n\n`;

        waMessage += `METODE DAN WAKTU:\n`;
        waMessage += `- Tipe Pesanan: *${orderType.toUpperCase()}*\n`;
        if (orderType === 'delivery') {
            waMessage += `- Alamat Pengiriman: ${address}\n`;
        }
        waMessage += `- Waktu Siap: *${dateVal} ${hourVal} WIB*\n\n`;

        waMessage += `ITEM PESANAN:\n${itemLines}\n`;

        waMessage += `SKEMA PEMBAYARAN:\n`;
        waMessage += `- Subtotal Item: Rp ${new Intl.NumberFormat('id-ID').format(subtotal)}\n`;
        waMessage += `- Pajak dan Layanan (10%): Rp ${new Intl.NumberFormat('id-ID').format(taxAmount)}\n`;
        waMessage += `- Total Tagihan: *Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}*\n`;
        waMessage += `- Skema: *${paymentPlan === 'dp' ? 'Uang Muka (DP 50%)' : 'Bayar Lunas (Full)'}*\n`;
        waMessage += `- *Wajib Bayar Sekarang: Rp ${new Intl.NumberFormat('id-ID').format(payNow)}*\n\n`;

        if (notes && notes.trim() !== '') {
            waMessage += `Catatan Tambahan: "${notes}"\n\n`;
        }

        waMessage += `Mohon info nomor rekening bank untuk transfer ya Kak. Terima kasih!`;

        const waUrl = `https://wa.me/${ADMIN_WA}?text=${encodeURIComponent(waMessage)}`;

        // 2. Submit Form ke Backend Laravel (Menyimpan Data Order)
        const form = document.getElementById('mainCheckoutForm');
        
        // Buka WhatsApp di tab baru lalu submit form
        window.open(waUrl, '_blank');
        form.submit();
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

        const taxAmount = Math.round(subtotal * TAX_RATE);
        const grandTotal = subtotal + taxAmount;

        document.getElementById('summarySubtotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        document.getElementById('summaryTax').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(taxAmount);
        document.getElementById('summaryTotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);

        const selectedPlan = document.querySelector('input[name="payment_plan"]:checked').value;
        const dpRow = document.getElementById('dpRowSummary');

        if (selectedPlan === 'dp') {
            const dpCalculation = Math.round(grandTotal * 0.5);
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