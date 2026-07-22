<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $produk->nama_produk }} - Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- SWIPER --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>

    <style>
        .glass{
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.25);
        }
        .gold-text {
            background: linear-gradient(135deg, #e6c89c, #c8a97e, #a67c52);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="bg-[#f5f0ea] text-[#3e2723] min-h-screen relative pb-20">

@include('layouts.navbar')

@php
    $folder = public_path('img/menu/' . $produk->gambar);
    $files = file_exists($folder) ? scandir($folder) : [];
    $images = array_values(array_diff($files, ['.', '..']));
@endphp

<main class="pt-24 md:pt-32 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

        {{-- GRID LAYOUT --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10">

            {{-- ================= LEFT : INTERACTIVE IMAGE SLIDER ================= --}}
            <div class="lg:sticky lg:top-28 h-fit">
                
                <div class="bg-white shadow-xl overflow-hidden rounded-3xl border border-white/40">
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach($images as $img)
                            <div class="swiper-slide">
                                <div class="w-full bg-[#f8f8f8] flex items-center justify-center">
                                    <img src="{{ asset('img/menu/' . $produk->gambar . '/' . $img) }}"
                                         class="w-full aspect-square object-cover">
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>

                {{-- THUMBNAIL TRACKS --}}
                <div class="flex gap-3 overflow-x-auto mt-4 py-1">
                    @foreach($images as $index => $img)
                    <div class="thumbnail-item min-w-[80px] w-20 h-20 rounded-2xl overflow-hidden border-2 border-white shadow-md cursor-pointer hover:border-[#c8a97e] transition"
                         data-index="{{ $index }}">
                        <img src="{{ asset('img/menu/' . $produk->gambar . '/' . $img) }}" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>

            </div>

            {{-- ================= RIGHT : PRODUCT METADATA SPECIFICATION ================= --}}
            <div class="h-fit">
                <div class="glass border border-white/40 shadow-xl rounded-3xl p-6 md:p-8 space-y-6">
                    
                    {{-- BACK BUTTON --}}
                    <div>
                        <a href="{{ route('menu') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#6b4f4f] hover:text-[#3e2723] transition">
                            <i class="fa-solid fa-arrow-left"></i> Kembali ke Menu
                        </a>
                    </div>

                    {{-- TEXT DATA PUSAT --}}
                    <div class="space-y-2">
                        <h1 class="text-2xl md:text-4xl font-bold leading-tight tracking-wide">
                            {{ $produk->nama_produk }}
                        </h1>
                        
                        <div class="pt-1">
                            <p class="text-xs text-[#8b6f63] font-bold uppercase tracking-wider">Harga Item</p>
                            <h2 class="text-3xl md:text-4xl font-black gold-text mt-0.5">
                                Rp {{ number_format($produk->harga, 0, ',', '.') }}
                            </h2>
                        </div>
                    </div>

                    {{-- DESCRIPTION INFORMATION --}}
                    <div class="border-t border-white/30 pt-5 space-y-2">
                        <h3 class="text-base font-bold text-[#3e2723] uppercase tracking-wide">Deskripsi Produk</h3>
                        <p class="text-[#6b4f4f] leading-relaxed text-sm md:text-base">
                            {{ $produk->deskripsi }}
                        </p>
                    </div>

                    {{-- TRANSACTION ACTION CART TRIGGER --}}
                    <div class="pt-4">
                        <button onclick="addSingleProductToCart()" 
                                class="w-full py-4 bg-[#3e2723] text-white font-bold rounded-2xl shadow-xl hover:bg-[#2c1b18] hover:scale-[1.01] transition duration-300 flex items-center justify-center gap-2 text-base">
                            <i class="fa-solid fa-basket-shopping text-lg"></i> Masukkan ke Keranjang Belanja
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</main>

{{-- ================= FLOATING BASKET TRIGGER ================= --}}
<div class="fixed bottom-6 right-6 z-40">
    <button onclick="toggleCartDrawer()" class="relative w-16 h-16 rounded-full bg-[#3e2723] text-white flex items-center justify-center shadow-2xl hover:scale-105 transition duration-300">
        <i class="fa-solid fa-cart-shopping text-xl"></i>
        <span id="cartCountBadge" class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-r from-[#c8a97e] to-[#b8860b] text-white text-xs font-bold rounded-full flex items-center justify-center hidden shadow-md">0</span>
    </button>
</div>

{{-- ================= SIDE DRAWER BASKET LAYER ================= --}}
<div id="cartDrawer" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white/40 backdrop-blur-3xl border-l border-white/40 shadow-[0_0_60px_rgba(0,0,0,0.2)] p-6 flex flex-col justify-between transform translate-x-full transition-transform duration-500">
    <div>
        <div class="flex justify-between items-center pb-4 border-b border-[#3e2723]/20">
            <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-solid fa-basket-shopping mr-2"></i> Keranjang Pre-Order</h3>
            <button onclick="toggleCartDrawer()" class="w-8 h-8 rounded-full bg-white/40 flex items-center justify-center font-bold text-[#3e2723] hover:bg-white/70">✕</button>
        </div>
        
        <div id="cartItemsList" class="overflow-y-auto max-h-[60vh] mt-4 space-y-3 pr-1">
            <p class="text-sm text-gray-500 text-center py-8">Keranjang belanja Anda kosong.</p>
        </div>
    </div>

    <div class="pt-4 border-t border-[#3e2723]/20 space-y-4">
        <div class="flex justify-between items-center font-bold text-lg text-[#3e2723]">
            <span>Total Tagihan:</span>
            <span id="cartTotalPrice">Rp 0</span>
        </div>
        
        {{-- MODIFIKASI FORM CHECKOUT MENJADI GET KE CHECKOUT.INDEX --}}
        <form action="{{ route('checkout.index') }}" method="GET" id="checkoutForm">
            <button type="submit" id="checkoutBtn" disabled class="w-full py-3.5 bg-[#3e2723] text-white font-semibold rounded-2xl shadow-xl hover:bg-[#2c1b18] disabled:bg-gray-400 disabled:scale-100 disabled:shadow-none transition duration-300 text-center block">
                <i class="fa-solid fa-credit-card mr-2"></i> Lanjutkan Ke Pre-Order
            </button>
        </form>
    </div>
</div>

@include('layouts.footer')

<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

<script>
    // Inisialisasi Swiper Slider Gambar Utama
    const swiper = new Swiper(".mySwiper", {
        loop: true,
        spaceBetween: 20,
        pagination: {
            el: ".swiper-pagination",
            clickable: true
        }
    });

    // Menangani sinkronisasi ketika item thumbnail diklik
    document.querySelectorAll('.thumbnail-item').forEach((thumb) => {
        thumb.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            // Karena menggunakan loop:true, indeks asli bergeser +1 pada slide kontainer internal Swiper
            swiper.slideTo(index + 1);
            this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        });
    });

    // ================= SCRIPT INTERAKTIF KERANJANG STORAGE ENGINE =================
    let cart = [];

    // Mengambil state autentikasi dari Laravel Blade ke variabel JavaScript global
    const isAuthenticated = @json(auth()->check());
    const loginUrl = "{{ route('login') }}";

    // Mengambil state data dari SessionStorage browser agar sinkron antar halaman
    if (sessionStorage.getItem('bakery_cart')) {
        cart = JSON.parse(sessionStorage.getItem('bakery_cart'));
        setTimeout(() => { updateCartUI(); }, 100);
    }

    function toggleCartDrawer() {
        const drawer = document.getElementById('cartDrawer');
        drawer.classList.toggle('translate-x-full');
    }

    function addSingleProductToCart() {
        const pId = {{ $produk->id }};
        const pName = "{{ $produk->nama_produk }}";
        const pPrice = {{ $produk->harga }};
        const pImg = "{{ count($images) > 0 ? asset('img/menu/' . $produk->gambar . '/' . $images[0]) : '' }}";
        
        const existingItem = cart.find(item => item.id === pId);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id: pId, name: pName, price: pPrice, image: pImg, quantity: 1 });
        }
        
        updateCartUI();
        toggleCartDrawer();
    }

    function updateQuantity(id, amount) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.quantity += amount;
            if (item.quantity <= 0) {
                cart = cart.filter(c => c.id !== id);
            }
        }
        updateCartUI();
    }

    // ================= INTERSEPSI TOMBOL PRE-ORDER JIKA USER BELUM LOGIN =================
    function handleCheckoutGuard(event) {
        if (!isAuthenticated) {
            event.preventDefault(); // Mencegah form disubmit secara default
            window.location.href = loginUrl; // Paksa arahkan ke rute login /edelweiss-admin
            return false;
        }
        return true; // Jika sudah login, izinkan form submit menuju halaman checkout backend
    }

    function updateCartUI() {
        const listContainer = document.getElementById('cartItemsList');
        const badge = document.getElementById('cartCountBadge');
        const totalContainer = document.getElementById('cartTotalPrice');
        // const hiddenInput = document.getElementById('cartDataHiddenInput'); // Tidak lagi digunakan karena method GET
        const checkoutBtn = document.getElementById('checkoutBtn');

        listContainer.innerHTML = '';
        sessionStorage.setItem('bakery_cart', JSON.stringify(cart));
        
        if (cart.length === 0) {
            listContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">Keranjang belanja Anda kosong.</p>';
            badge.classList.add('hidden');
            totalContainer.innerText = 'Rp 0';
            // hiddenInput.value = ''; // Tidak lagi digunakan
            checkoutBtn.disabled = true;
            return;
        }

        let totalItems = 0;
        let totalPrice = 0;

        cart.forEach(item => {
            totalItems += item.quantity;
            totalPrice += item.price * item.quantity;

            const row = document.createElement('div');
            row.className = "flex items-center gap-3 p-3 bg-white/40 border border-white/50 rounded-2xl shadow-sm";
            row.innerHTML = `
                <img src="${item.image ? item.image : '/img/logo/logo2.png'}" class="w-12 h-12 object-cover rounded-xl bg-gray-100">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-[#3e2723] truncate">${item.name}</p>
                    <p class="text-xs gold-text font-semibold">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</p>
                </div>
                <div class="flex items-center gap-2 bg-white/60 border rounded-xl px-2 py-1">
                    <button onclick="updateQuantity(${item.id}, -1)" class="font-bold text-[#3e2723] hover:text-red-600">-</button>
                    <span class="text-xs font-bold px-1">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, 1)" class="font-bold text-[#3e2723] hover:text-green-600">+</button>
                </div>
            `;
            listContainer.appendChild(row);
        });

        badge.innerText = totalItems;
        badge.classList.remove('hidden');
        totalContainer.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalPrice);
        
        // hiddenInput.value = JSON.stringify(cart); // Tidak lagi digunakan
        checkoutBtn.disabled = false;
    }
</script>

</body>
</html>