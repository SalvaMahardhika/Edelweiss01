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

        /* Hide Number Input Spinners for sleek UI */
        input[type='number']::-webkit-inner-spin-button,
        input[type='number']::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type='number'] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723] min-h-screen relative pb-20">

@include('layouts.navbar')

{{-- 🔔 TOAST NOTIFICATION POPUP (SAMPLING DISAMPING) --}}
<div id="cartToast" class="fixed top-24 right-4 z-50 transform translate-x-full opacity-0 transition-all duration-500 ease-in-out pointer-events-none">
    <div class="flex items-center gap-3 px-4 py-3 bg-[#3e2723]/95 backdrop-blur-xl border border-[#c8a97e]/40 text-white rounded-2xl shadow-2xl max-w-xs sm:max-w-sm">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#c8a97e] to-[#a67c52] flex items-center justify-center text-white shrink-0 shadow-inner">
            <i class="fa-solid fa-basket-shopping text-base"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-[#e6c89c]">Berhasil Ditambahkan</p>
            <p id="toastMessage" class="text-xs text-gray-200 truncate font-medium">1x Item dimasukkan ke keranjang</p>
        </div>
        <button onclick="hideCartToast()" class="text-gray-400 hover:text-white text-xs p-1 pointer-events-auto">✕</button>
    </div>
</div>

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
                // PENGECEKAN PATH DYNAMIC UNTUK PUBLIC_HTML HOSTING DAN LOCALHOST
                $publicHtmlFolder = base_path('../public_html/img/menu/' . $item->gambar);
                $localFolder = public_path('img/menu/' . $item->gambar);

                if (file_exists($publicHtmlFolder)) {
                    $folder = $publicHtmlFolder;
                } else {
                    $folder = $localFolder;
                }

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
                <div class="p-3 sm:p-5 bg-white/30 backdrop-blur-xl flex flex-col justify-between min-h-[160px]">
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

                    {{-- 🟢 INPUT JUMLAH & TOMBOL TAMBAH KERANJANG --}}
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-center border border-white/60 bg-white/50 rounded-xl overflow-hidden shadow-inner">
                            <button type="button" onclick="adjustProductQty({{ $item->id }}, -1)" class="px-3 py-1.5 text-sm font-bold text-[#3e2723] hover:bg-white/80 transition">-</button>
                            <input type="number" id="card_qty_{{ $item->id }}" value="1" min="1" class="w-12 text-center text-xs sm:text-sm font-bold bg-transparent text-[#3e2723] focus:outline-none">
                            <button type="button" onclick="adjustProductQty({{ $item->id }}, 1)" class="px-3 py-1.5 text-sm font-bold text-[#3e2723] hover:bg-white/80 transition">+</button>
                        </div>

                        <button onclick="addToCartFromCard({{ $item->id }}, '{{ addslashes($item->nama_produk) }}', {{ $item->harga }}, '{{ count($images) > 0 ? asset('img/menu/' . $item->gambar . '/' . $images[0]) : '' }}')"
                                class="w-full py-2 text-xs sm:text-sm font-bold text-white bg-[#3e2723] hover:bg-[#2c1b18] rounded-xl shadow-md transition duration-300 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-basket-shopping"></i> + Keranjang
                        </button>
                    </div>
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
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-solid fa-basket-shopping mr-1"></i> Keranjang Pre-Order</h3>
                {{-- 🔴 TOMBOL MEMBUKA MODAL BERSIHKAN KERANJANG --}}
                <button type="button" id="clearCartBtn" onclick="openClearCartModal()" class="hidden text-xs font-bold text-rose-700 bg-rose-100 hover:bg-rose-200 px-2.5 py-1 rounded-lg transition flex items-center gap-1 shadow-sm" title="Kosongkan seluruh isi keranjang">
                    <i class="fa-solid fa-trash-can text-[11px]"></i> Bersihkan
                </button>
            </div>
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
        
        {{-- FORM CHECKOUT --}}
        <form action="{{ route('checkout.index') }}" method="GET" id="checkoutForm" onsubmit="return handleCheckoutSubmit(event)">
            <button type="submit" id="checkoutBtn" disabled class="w-full py-3.5 bg-[#3e2723] text-white font-semibold rounded-2xl shadow-xl hover:bg-[#2c1b18] disabled:bg-gray-400 disabled:scale-100 disabled:shadow-none transition duration-300 text-center block">
                <i class="fa-solid fa-credit-card mr-2"></i> Lanjutkan Ke Pre-Order
            </button>
        </form>
    </div>
</div>

{{-- 🗑️ MODAL MANDIRI KONFIRMASI BERSIHKAN KERANJANG --}}
<div id="clearCartModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-md flex items-center justify-center p-4 hidden transition-opacity duration-300">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/90 backdrop-blur-2xl border border-white/80 shadow-2xl text-center space-y-4">
        <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        
        <div>
            <h3 class="text-lg font-black text-[#3e2723]">Kosongkan Keranjang?</h3>
            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                Apakah Anda yakin ingin menghapus seluruh pesanan dari keranjang belanja Anda?
            </p>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="button" onclick="closeClearCartModal()" class="w-1/2 py-2.5 text-xs font-bold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-xl transition">
                Batal
            </button>
            <button type="button" onclick="confirmClearCart()" class="w-1/2 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-lg transition">
                Ya, Hapus Semua
            </button>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
    // State Global Filter & Keranjang
    let activeCategory = 'all';
    let cart = [];
    let toastTimeout = null;

    // ================= SCRIPT LOGIK LIVE FILTER (SEARCH & CATEGORY) =================
    function filterCategory(catId, btnElement) {
        activeCategory = catId;
        
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.className = "category-btn px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition duration-300 bg-white/40 backdrop-blur-xl border border-white/50 text-[#3e2723] hover:bg-white/70";
        });
        
        btnElement.className = "category-btn px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition duration-300 bg-[#3e2723] text-white";

        filterMenu();
    }

    function filterMenu() {
        const query = document.getElementById('menuSearchInput').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const category = card.getAttribute('data-category');

            const matchesSearch = name.includes(query);
            const matchesCategory = (activeCategory === 'all' || category === activeCategory);

            if (matchesSearch && matchesCategory) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        const notice = document.getElementById('emptySearchNotice');
        if (visibleCount === 0) {
            notice.classList.remove('hidden');
        } else {
            notice.classList.add('hidden');
        }
    }

    // ================= SCRIPT INPUT JUMLAH PADA KARTU PRODUK =================
    function adjustProductQty(id, change) {
        const input = document.getElementById(`card_qty_${id}`);
        if (!input) return;
        let val = parseInt(input.value) || 1;
        val += change;
        if (val < 1) val = 1;
        input.value = val;
    }

    // 🟢 DITAMBAHKAN NOTIFIKASI TOAST POPUP (TANPA MEMBUKA CART DRAWER)
    function addToCartFromCard(id, name, price, image) {
        const input = document.getElementById(`card_qty_${id}`);
        const qtyToAdd = input ? (parseInt(input.value) || 1) : 1;

        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += qtyToAdd;
        } else {
            cart.push({ id, name, price, image, quantity: qtyToAdd });
        }

        // Reset input jumlah kartu kembali ke 1
        if (input) input.value = 1;

        updateCartUI();

        // 🟢 Munculkan Notifikasi Toast Melayang disamping tanpa membuka drawer
        showCartToast(`${qtyToAdd}x ${name} ditambahkan`);
    }

    function showCartToast(msg) {
        const toast = document.getElementById('cartToast');
        const toastMsg = document.getElementById('toastMessage');

        if (!toast) return;

        toastMsg.innerText = msg;
        toast.classList.remove('translate-x-full', 'opacity-0');
        toast.classList.add('translate-x-0', 'opacity-100');

        if (toastTimeout) clearTimeout(toastTimeout);

        toastTimeout = setTimeout(() => {
            hideCartToast();
        }, 2500);
    }

    function hideCartToast() {
        const toast = document.getElementById('cartToast');
        if (!toast) return;
        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-full', 'opacity-0');
    }

    // ================= SCRIPT LOGIK KERANJANG STORAGE =================
    if (sessionStorage.getItem('bakery_cart')) {
        try {
            cart = JSON.parse(sessionStorage.getItem('bakery_cart')) || [];
        } catch(e) {
            cart = [];
        }
        setTimeout(() => { updateCartUI(); }, 100);
    }

    function toggleCartDrawer() {
        const drawer = document.getElementById('cartDrawer');
        drawer.classList.toggle('translate-x-full');
    }

    // 🗑️ MODAL MANDIRI LOGIKA BERSIHKAN KERANJANG
    function openClearCartModal() {
        if (cart.length === 0) return;
        document.getElementById('clearCartModal').classList.remove('hidden');
    }

    function closeClearCartModal() {
        document.getElementById('clearCartModal').classList.add('hidden');
    }

    function confirmClearCart() {
        cart = [];
        sessionStorage.removeItem('bakery_cart');
        updateCartUI();
        closeClearCartModal();
    }

    // Mengubah jumlah item langsung dari input teks keranjang
    function setItemQuantity(id, newQty) {
        let val = parseInt(newQty);
        if (isNaN(val) || val <= 0) {
            cart = cart.filter(c => c.id !== id);
        } else {
            const item = cart.find(i => i.id === id);
            if (item) item.quantity = val;
        }
        updateCartUI();
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

    // 🟢 SUBMIT CHECKOUT (MENJAGA KERANJANG TETAP ADA SAAT PINDAH HALAMAN)
    function handleCheckoutSubmit(event) {
        if (cart.length === 0) {
            event.preventDefault();
            return false;
        }

        // Simpan versi data keranjang terbaru ke sessionStorage agar dibaca di halaman checkout.blade
        sessionStorage.setItem('bakery_cart', JSON.stringify(cart));

        // IZINKAN FORM SUBMIT TANPA MENGHAPUS STORAGE AGAR TIDAK MEMUTUSKAN ISI KERANJANG
        return true;
    }

    function updateCartUI() {
        const listContainer = document.getElementById('cartItemsList');
        const badge = document.getElementById('cartCountBadge');
        const totalContainer = document.getElementById('cartTotalPrice');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const clearCartBtn = document.getElementById('clearCartBtn');

        listContainer.innerHTML = '';
        sessionStorage.setItem('bakery_cart', JSON.stringify(cart));
        
        if (cart.length === 0) {
            listContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">Keranjang belanja Anda kosong.</p>';
            badge.classList.add('hidden');
            if (clearCartBtn) clearCartBtn.classList.add('hidden');
            totalContainer.innerText = 'Rp 0';
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
                <img src="${item.image ? item.image : '/img/logo/logo2.png'}" class="w-12 h-12 object-cover rounded-xl bg-gray-100 shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-[#3e2723] truncate">${item.name}</p>
                    <p class="text-xs gold-text font-semibold">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</p>
                </div>
                <div class="flex items-center gap-1 bg-white/60 border rounded-xl px-2 py-1 shadow-inner">
                    <button type="button" onclick="updateQuantity(${item.id}, -1)" class="font-bold text-[#3e2723] hover:text-red-600 px-1.5">-</button>
                    <input type="number" min="1" value="${item.quantity}" onchange="setItemQuantity(${item.id}, this.value)" class="w-10 text-center text-xs font-bold bg-transparent text-[#3e2723] focus:outline-none">
                    <button type="button" onclick="updateQuantity(${item.id}, 1)" class="font-bold text-[#3e2723] hover:text-green-600 px-1.5">+</button>
                </div>
            `;
            listContainer.appendChild(row);
        });

        badge.innerText = totalItems;
        badge.classList.remove('hidden');
        if (clearCartBtn) clearCartBtn.classList.remove('hidden');
        totalContainer.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalPrice);
        
        checkoutBtn.disabled = false;
    }
</script>

</body>
</html>