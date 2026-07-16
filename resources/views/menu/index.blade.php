<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }

        /* ✨ TEXT GLOW */
        .glow-hover {
            transition: all 0.3s ease;
        }

        .group:hover .glow-hover {
            text-shadow: 0 0 12px rgba(255,255,255,0.6),
                         0 0 20px rgba(200,169,126,0.6);
        }

        /* 🟡 GOLD GRADIENT TEXT */
        .gold-text {
            background: linear-gradient(135deg, #e6c89c, #c8a97e, #a67c52);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* 💎 GLASS SHINE */
        .glass-shine::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                120deg,
                transparent 30%,
                rgba(255,255,255,0.4),
                transparent 70%
            );
            transform: rotate(25deg);
            transition: all 0.6s ease;
        }

        .group:hover .glass-shine::before {
            top: 100%;
            left: 100%;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723] min-h-screen relative pb-20">

@include('layouts.navbar')

<main class="relative overflow-hidden pt-32">

    {{-- BACKGROUND GLOW --}}
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-40 left-1/3 w-96 h-96 bg-[#c8a97e]/20 blur-3xl rounded-full"></div>
        <div class="absolute bottom-40 right-1/3 w-96 h-96 bg-[#3e2723]/20 blur-3xl rounded-full"></div>
    </div>

    {{-- TITLE --}}
    <section class="max-w-6xl mx-auto px-6 text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-semibold tracking-wide glow-hover">
            Pilihan Menu Kami
        </h1>
        <p class="text-[#6b4f4f] mt-2">
            Fresh & handmade dengan kualitas terbaik untuk Pre-Order Anda
        </p>
    </section>

    {{-- ================= SEARCH & CATEGORY FILTER CONTROL ================= --}}
    <section class="max-w-4xl mx-auto px-4 mb-12 space-y-6">
        {{-- SEARCH BAR --}}
        <div class="relative max-w-xl mx-auto">
            <input type="text" id="menuSearchInput" onkeyup="filterMenu()"
                   placeholder="Cari kue favorit Anda... (misal: 'apel')"
                   class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-white/40 backdrop-blur-xl border border-white/50 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner text-[#3e2723] placeholder-[#3e2723]/50 transition duration-300">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#3e2723]/60">
                <i class="fa-solid fa-magnifying-glass text-lg"></i>
            </div>
        </div>

        {{-- DYNAMIC CATEGORY FILTER TABS --}}
        <div class="flex flex-wrap justify-center gap-2 sm:gap-3">
            <button onclick="filterCategory('all', this)" 
                    class="category-btn px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition duration-300 bg-[#3e2723] text-white">
                Semua Menu
            </button>
            @foreach($categories as $cat)
            <button onclick="filterCategory('{{ $cat->id }}', this)" 
                    class="category-btn px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition duration-300 bg-white/40 backdrop-blur-xl border border-white/50 text-[#3e2723] hover:bg-white/70">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>
    </section>

    {{-- GRID KATALOG PRODUK --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-20 sm:pb-24">
        {{-- EMPTY STATE NOTICE --}}
        <div id="emptySearchNotice" class="hidden text-center py-16">
            <div class="text-4xl text-gray-400 mb-3"><i class="fa-solid fa-cookie-bite"></i></div>
            <p class="text-sm text-gray-500">Menu yang Anda cari tidak ditemukan.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            @foreach($produk->where('status', true) as $item)

            @php
                $folder = public_path('img/menu/' . $item->gambar);
                $files = file_exists($folder) ? scandir($folder) : [];
                $images = array_values(array_diff($files, ['.', '..']));
            @endphp

            {{-- CARD PRODUK DENGAN METADATA FILTER --}}
            <div class="product-card relative group glass-shine backdrop-blur-2xl bg-white/30 border border-white/40 rounded-2xl sm:rounded-3xl overflow-hidden shadow-[0_10px_40px_rgba(0,0,0,0.15)] transition duration-500 hover:scale-[1.03]"
                 data-name="{{ strtolower($item->nama_produk) }}"
                 data-category="{{ $item->category_id }}">

                {{-- IMAGE --}}
                <div class="relative overflow-hidden h-40 sm:h-52 md:h-56">
                    @if(count($images) > 0)
                    <img src="{{ asset('img/menu/' . $item->gambar . '/' . $images[0]) }}"
                         class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    @else
                    <div class="w-full h-full bg-[#3e2723]/10 flex items-center justify-center text-xs text-gray-400">Tidak ada gambar</div>
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>

                    {{-- HOVER ACTION DETAIL --}}
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                        <a href="{{ route('menu.show', $item->encrypted_id) }}"
                           class="px-4 py-2 rounded-xl bg-white/80 backdrop-blur text-[#3e2723] text-xs sm:text-sm font-semibold shadow hover:scale-105 transition">
                            Detail →
                        </a>
                    </div>
                </div>

                {{-- CONTENT DATA --}}
                <div class="p-3 sm:p-5 bg-white/30 backdrop-blur-xl flex flex-col justify-between min-h-[140px]">
                    <div>
                        <div class="flex flex-col mb-2">
                            <h3 class="product-title font-bold text-sm sm:text-base text-[#2d1f1b] line-clamp-1">
                                {{ $item->nama_produk }}
                            </h3>
                            <span class="text-xs sm:text-sm font-semibold gold-text mt-0.5">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </span>
                        </div>
                        <p class="text-[#5c4033] text-xs line-clamp-2 leading-relaxed">
                            {{ $item->deskripsi }}
                        </p>
                    </div>

                    {{-- ADD TO CART BUTTON --}}
                    <button onclick="addToCart({{ $item->id }}, '{{ $item->nama_produk }}', {{ $item->harga }}, '{{ count($images) > 0 ? asset('img/menu/' . $item->gambar . '/' . $images[0]) : '' }}')"
                            class="w-full mt-3 py-2 text-xs sm:text-sm font-bold text-white bg-[#3e2723] hover:bg-[#2c1b18] rounded-xl shadow-md transition duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-basket-shopping"></i> + Keranjang
                    </button>
                </div>
            </div>

            @endforeach
        </div>
    </section>
</main>

{{-- ================= FLOATING BASKET BUTTON ================= --}}
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
        
        <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
            @csrf
            <input type="hidden" name="cart_data" id="cartDataHiddenInput">
            <button type="submit" id="checkoutBtn" disabled class="w-full py-3.5 bg-[#3e2723] text-white font-semibold rounded-2xl shadow-xl hover:bg-[#2c1b18] disabled:bg-gray-400 disabled:scale-100 disabled:shadow-none transition duration-300 text-center block">
                <i class="fa-solid fa-credit-card mr-2"></i> Lanjutkan Ke Pre-Order
            </button>
        </form>
    </div>
</div>

@include('layouts.footer')

<script>
    // State Global Filter
    let activeCategory = 'all';
    let cart = [];

    // ================= SCRIPT LOGIK LIVE FILTER (SEARCH & CATEGORY) =================
    function filterCategory(catId, btnElement) {
        activeCategory = catId;
        
        // Ubah Style Active Button Tab Kategori
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.className = "category-btn px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition duration-300 bg-white/40 backdrop-blur-xl border border-white/50 text-[#3e2723] hover:bg-white/70";
        });
        
        if (catId === 'all') {
            btnElement.className = "category-btn px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition duration-300 bg-[#3e2723] text-white";
        } else {
            btnElement.className = "category-btn px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition duration-300 bg-[#3e2723] text-white";
        }

        filterMenu();
    }

    function filterMenu() {
        const query = document.getElementById('menuSearchInput').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const category = card.getAttribute('data-category');

            // Cek kecocokan Substring nama kue DAN filter kategori aktif
            const matchesSearch = name.includes(query);
            const matchesCategory = (activeCategory === 'all' || category === activeCategory);

            if (matchesSearch && matchesCategory) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Tampilkan notice jika pencarian kosong
        const notice = document.getElementById('emptySearchNotice');
        if (visibleCount === 0) {
            notice.classList.remove('hidden');
        } else {
            notice.classList.add('hidden');
        }
    }

    // ================= SCRIPT LOGIK KERANJANG STORAGE =================
    if (sessionStorage.getItem('bakery_cart')) {
        cart = JSON.parse(sessionStorage.getItem('bakery_cart'));
        setTimeout(() => { updateCartUI(); }, 100);
    }

    function toggleCartDrawer() {
        const drawer = document.getElementById('cartDrawer');
        drawer.classList.toggle('translate-x-full');
    }

    function addToCart(id, name, price, image) {
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id, name, price, image, quantity: 1 });
        }
        updateCartUI();
        const drawer = document.getElementById('cartDrawer');
        if(drawer.classList.contains('translate-x-full')) toggleCartDrawer();
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

    function updateCartUI() {
        const listContainer = document.getElementById('cartItemsList');
        const badge = document.getElementById('cartCountBadge');
        const totalContainer = document.getElementById('cartTotalPrice');
        const hiddenInput = document.getElementById('cartDataHiddenInput');
        const checkoutBtn = document.getElementById('checkoutBtn');

        listContainer.innerHTML = '';
        sessionStorage.setItem('bakery_cart', JSON.stringify(cart));
        
        if (cart.length === 0) {
            listContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">Keranjang belanja Anda kosong.</p>';
            badge.classList.add('hidden');
            totalContainer.innerText = 'Rp 0';
            hiddenInput.value = '';
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
        
        hiddenInput.value = JSON.stringify(cart);
        checkoutBtn.disabled = false;
    }
</script>

</body>
</html>