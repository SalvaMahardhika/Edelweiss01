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

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .glass{
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.25);
        }
        .gold-text {
            background: linear-gradient(135deg, #e6c89c, #c8a97e, #a67c52);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

<body class="bg-[#f5f0ea] text-[#3e2723] min-h-screen relative pb-20">

@include('layouts.navbar')

{{-- 🔔 TOAST NOTIFICATION POPUP --}}
<div id="cartToast" class="fixed top-24 right-4 z-50 transform translate-x-full opacity-0 transition-all duration-500 ease-in-out pointer-events-none">
    <div class="flex items-center gap-3 px-4 py-3 bg-[#3e2723]/95 backdrop-blur-xl border border-[#c8a97e]/40 text-white rounded-2xl shadow-2xl max-w-xs sm:max-w-sm">
        <div id="toastIconContainer" class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#c8a97e] to-[#a67c52] flex items-center justify-center text-white shrink-0 shadow-inner">
            <i id="toastIcon" class="fa-solid fa-basket-shopping text-base"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p id="toastTitle" class="text-xs font-bold text-[#e6c89c]">Berhasil Ditambahkan</p>
            <p id="toastMessage" class="text-xs text-gray-200 truncate font-medium">1x Item dimasukkan ke keranjang</p>
        </div>
        <button onclick="hideCartToast()" class="text-gray-400 hover:text-white text-xs p-1 pointer-events-auto">✕</button>
    </div>
</div>

<main class="pt-24 md:pt-32 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

        {{-- ⚡ CONTAINER UTAMA UNTUK REALTIME SYNC DETAIL MENU --}}
        <div id="productDetailContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10" data-status="{{ $produk->status ? '1' : '0' }}">

            @php
                // PENGECEKAN PATH DYNAMIC UNTUK PUBLIC_HTML HOSTING DAN LOCALHOST
                $publicHtmlFolder = base_path('../public_html/img/menu/' . $produk->gambar);
                $localFolder = public_path('img/menu/' . $produk->gambar);

                if (file_exists($publicHtmlFolder)) {
                    $folder = $publicHtmlFolder;
                } else {
                    $folder = $localFolder;
                }

                $files = file_exists($folder) ? scandir($folder) : [];
                $images = array_values(array_diff($files, ['.', '..']));
            @endphp

            {{-- ================= LEFT : INTERACTIVE IMAGE SLIDER ================= --}}
            <div class="lg:sticky lg:top-28 h-fit">
                
                <div class="bg-white shadow-xl overflow-hidden rounded-3xl border border-white/40">
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @forelse($images as $img)
                            <div class="swiper-slide">
                                <div class="w-full bg-[#f8f8f8] flex items-center justify-center">
                                    <img src="{{ asset('img/menu/' . $produk->gambar . '/' . $img) }}"
                                         class="w-full aspect-square object-cover">
                                </div>
                            </div>
                            @empty
                            <div class="swiper-slide">
                                <div class="w-full h-80 bg-[#f8f8f8] flex items-center justify-center text-xs text-gray-400">
                                    Tidak ada gambar
                                </div>
                            </div>
                            @endforelse
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

                    {{-- DESCRIPTION INFORMATION (MENAMPILKAN FORMAT PARAGRAF MULTILINE) --}}
                    <div class="border-t border-white/30 pt-5 space-y-2">
                        <h3 class="text-base font-bold text-[#3e2723] uppercase tracking-wide">Deskripsi Produk</h3>
                        <div class="text-[#6b4f4f] leading-relaxed text-sm md:text-base space-y-2">
                            {!! nl2br(e($produk->deskripsi)) !!}
                        </div>
                    </div>

                    {{-- 🟢 INPUT JUMLAH LANGSUNG & KONTROL TRANSAKSI --}}
                    <div class="pt-4 space-y-3 border-t border-white/30">
                        <p class="text-xs font-bold text-[#8b6f63] uppercase tracking-wider">Jumlah Pesanan</p>
                        
                        <div class="flex items-center w-36 border border-white/60 bg-white/50 rounded-2xl overflow-hidden shadow-inner p-1">
                            <button type="button" onclick="adjustDetailQty(-1)" class="w-10 h-10 flex items-center justify-center text-lg font-bold text-[#3e2723] hover:bg-white/80 rounded-xl transition">-</button>
                            <input type="number" id="detail_qty_input" value="1" min="1" class="w-16 text-center text-base font-bold bg-transparent text-[#3e2723] focus:outline-none">
                            <button type="button" onclick="adjustDetailQty(1)" class="w-10 h-10 flex items-center justify-center text-lg font-bold text-[#3e2723] hover:bg-white/80 rounded-xl transition">+</button>
                        </div>

                        <button id="addToCartBtn"
                                onclick="addSingleProductToCart({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', {{ $produk->harga }}, '{{ count($images) > 0 ? asset('img/menu/' . $produk->gambar . '/' . $images[0]) : '' }}')" 
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
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-solid fa-basket-shopping mr-1"></i> Keranjang Pre-Order</h3>
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

<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

<script>
    let swiper = null;

    function initSwiper() {
        if (swiper) {
            swiper.destroy(true, true);
        }
        swiper = new Swiper(".mySwiper", {
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
                if (swiper) swiper.slideTo(index + 1);
                this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            });
        });
    }

    // ================= SCRIPT INTERAKTIF KERANJANG STORAGE ENGINE =================
    let cart = [];
    let toastTimeout = null;
    let detailRealtimeTimer = null;

    // Mengambil state data dari SessionStorage browser agar sinkron antar halaman
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

    // Adjust jumlah kuantitas di halaman detail
    function adjustDetailQty(change) {
        const input = document.getElementById('detail_qty_input');
        if (!input) return;
        let val = parseInt(input.value) || 1;
        val += change;
        if (val < 1) val = 1;
        input.value = val;
    }

    // 🟢 TAMBAH DENGAN VALiDASI REALTIME TERHADAP STATUS PRODUK
    function addSingleProductToCart(pId, pName, pPrice, pImg) {
        const container = document.getElementById('productDetailContainer');
        const isProductActive = container ? container.getAttribute('data-status') === '1' : true;

        if (!isProductActive) {
            showCartToast("Produk ini sedang tidak tersedia.", "error");
            setTimeout(() => {
                window.location.href = "{{ route('menu') }}";
            }, 1200);
            return;
        }

        const qtyInput = document.getElementById('detail_qty_input');
        const qtyToAdd = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;

        const existingItem = cart.find(item => item.id === pId);
        if (existingItem) {
            existingItem.quantity += qtyToAdd;
        } else {
            cart.push({ id: pId, name: pName, price: pPrice, image: pImg, quantity: qtyToAdd });
        }
        
        // Reset input kuantitas halaman detail kembali ke 1
        if (qtyInput) qtyInput.value = 1;

        updateCartUI();
        showCartToast(`${qtyToAdd}x ${pName} ditambahkan`, "success");
    }

    function showCartToast(msg, type = "success") {
        const toast = document.getElementById('cartToast');
        const toastMsg = document.getElementById('toastMessage');
        const toastTitle = document.getElementById('toastTitle');
        const toastIconContainer = document.getElementById('toastIconContainer');
        const toastIcon = document.getElementById('toastIcon');

        if (!toast) return;

        toastMsg.innerText = msg;

        if (type === "error") {
            toastTitle.innerText = "Tidak Tersedia";
            toastTitle.className = "text-xs font-bold text-rose-300";
            toastIconContainer.className = "w-10 h-10 rounded-xl bg-rose-600 flex items-center justify-center text-white shrink-0 shadow-inner";
            toastIcon.className = "fa-solid fa-triangle-exclamation text-base";
        } else {
            toastTitle.innerText = "Berhasil Ditambahkan";
            toastTitle.className = "text-xs font-bold text-[#e6c89c]";
            toastIconContainer.className = "w-10 h-10 rounded-xl bg-gradient-to-br from-[#c8a97e] to-[#a67c52] flex items-center justify-center text-white shrink-0 shadow-inner";
            toastIcon.className = "fa-solid fa-basket-shopping text-base";
        }

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

    // Mengubah jumlah item langsung dari input angka di dalam drawer keranjang
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

        // Simpan data keranjang terbaru ke sessionStorage agar dapat dibaca di halaman checkout.blade
        sessionStorage.setItem('bakery_cart', JSON.stringify(cart));

        // Izinkan navigasi tanpa menghapus sessionStorage
        return true;
    }

    function updateCartUI() {
        const listContainer = document.getElementById('cartItemsList');
        const badge = document.getElementById('cartCountBadge');
        const totalContainer = document.getElementById('cartTotalPrice');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const clearCartBtn = document.getElementById('clearCartBtn');

        if (!listContainer) return;

        listContainer.innerHTML = '';
        sessionStorage.setItem('bakery_cart', JSON.stringify(cart));
        
        if (cart.length === 0) {
            listContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">Keranjang belanja Anda kosong.</p>';
            if (badge) badge.classList.add('hidden');
            if (clearCartBtn) clearCartBtn.classList.add('hidden');
            if (totalContainer) totalContainer.innerText = 'Rp 0';
            if (checkoutBtn) checkoutBtn.disabled = true;
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

        if (badge) {
            badge.innerText = totalItems;
            badge.classList.remove('hidden');
        }
        if (clearCartBtn) clearCartBtn.classList.remove('hidden');
        if (totalContainer) totalContainer.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalPrice);
        if (checkoutBtn) checkoutBtn.disabled = false;
    }

    // ================= ⚡ REALTIME SYNC DETAIL MENU ENGINE =================
    function syncRealtimeProductDetail() {
        const currentQtyInput = document.getElementById('detail_qty_input');
        const savedQtyValue = currentQtyInput ? currentQtyInput.value : 1;

        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            // 🚨 PENGECEKAN TEPAT STATUS HTTP: REDIRECT JIKA PRODUK DIHAPUS / DELETED / NOT FOUND
            if (response.status === 404 || response.redirected) {
                window.location.href = "{{ route('menu') }}";
                return null;
            }
            return response.text();
        })
        .then(html => {
            if (!html) return;

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newContent = doc.getElementById('productDetailContainer');
            const currentContent = document.getElementById('productDetailContainer');

            // 🚨 JIKA PRODUK DISINNYA DIUBAH MENJADI NON-AKTIF (STATUS = 0) OLEH ADMIN
            if (newContent) {
                const isNewStatusActive = newContent.getAttribute('data-status') === '1';
                if (!isNewStatusActive) {
                    window.location.href = "{{ route('menu') }}";
                    return;
                }
            } else {
                // Kontainer detail produk hilang / dirender berbeda (misal halaman error)
                window.location.href = "{{ route('menu') }}";
                return;
            }

            if (newContent && currentContent && newContent.innerHTML.trim() !== currentContent.innerHTML.trim()) {
                currentContent.innerHTML = newContent.innerHTML;
                
                // Inisialisasi ulang Swiper Slider & Thumbnail Listeners
                initSwiper();

                // Kembalikan nilai input kuantitas pelanggan
                const refreshedQtyInput = document.getElementById('detail_qty_input');
                if (refreshedQtyInput) {
                    refreshedQtyInput.value = savedQtyValue;
                }
            }
        })
        .catch(err => console.error("Realtime Detail Sync Error:", err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        initSwiper();

        // Background polling setiap 3 detik saat tab browser aktif
        if (detailRealtimeTimer) clearInterval(detailRealtimeTimer);
        detailRealtimeTimer = setInterval(function() {
            if (document.visibilityState === 'visible') {
                syncRealtimeProductDetail();
            }
        }, 3000);

        // Broadcast Listener (Laravel Echo)
        if (typeof Echo !== 'undefined') {
            Echo.channel('menu-updates')
                .listen('.menu.updated', (e) => {
                    syncRealtimeProductDetail();
                });
        }
    });
</script>

</body>
</html>