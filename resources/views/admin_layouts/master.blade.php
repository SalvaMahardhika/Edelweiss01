<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title') | Edelweiss Admin</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">
    
    {{-- Tailwind, AlpineJS & FontAwesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Custom Scrollbar for sleek look */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
<body class="bg-[#fafafa] text-[#3e2723] min-h-screen relative overflow-x-hidden">

    {{-- BACKGROUND ART & BLUR GLOW --}}
    <div class="fixed inset-0 z-0">
        <img src="{{ asset('img/dashboard/assets/3.png') }}" class="w-full h-full object-cover opacity-15 filter grayscale">
        <div class="absolute inset-0 bg-gradient-to-br from-[#fafafa] via-[#f5efe8] to-[#ede5dc]/90"></div>
        
        <!-- Ambient Liquid Glow Tokens -->
        <div class="absolute top-10 left-10 w-96 h-96 bg-[#d4af37] rounded-full blur-[120px] opacity-15"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-[#3e2723] rounded-full blur-[120px] opacity-10"></div>
    </div>

    {{-- MAIN WRAPPER INTERFACE --}}
    <div class="relative z-10 flex min-h-screen p-4 gap-4">
        
        {{-- SIDEBAR CONTAINER (Liquid Glass Card) --}}
        <aside class="w-72 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-2xl p-6 flex flex-col justify-between hidden lg:flex">
            <div>
                <!-- Branding -->
                <div class="flex items-center gap-3 px-2 mb-8">
                    <img src="{{ asset('img/logo/logo2.png') }}" class="h-10 w-auto drop-shadow-md">
                    <span class="font-black text-lg tracking-wider bg-gradient-to-r from-[#3e2723] to-[#b8860b] bg-clip-text text-transparent">ADMIN DASHBOARD</span>
                </div>
                
                <!-- Navigation Links -->
                <nav class="space-y-2">
                    {{-- Dashboard Summary Link --}}
                    <a href="{{ route('admin.index') }}" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition duration-300 {{ Route::is('admin.index') ? 'bg-[#3e2723] text-white font-semibold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-medium' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-lg"></i> Dashboard
                    </a>
                    
                    {{-- 🏷️ KATEGORI MENU --}}
                    <a href="{{ route('kategori.index') }}" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition duration-300 {{ Route::is('kategori.*') ? 'bg-[#3e2723] text-white font-semibold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-medium' }}">
                        <i class="fa-solid fa-tags w-5 text-center text-lg"></i> Kategori Menu
                    </a>

                    {{-- 📦 MANAJEMEN MENU --}}
                    <a href="{{ route('produk.index') }}" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition duration-300 {{ Route::is('produk.*') || Request::is('admin/menu*') ? 'bg-[#3e2723] text-white font-semibold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-medium' }}">
                        <i class="fa-solid fa-layer-group w-5 text-center text-lg"></i> Manajemen Menu
                    </a>

                    {{-- 🖼️ GALERI FOTO --}}
                    <a href="{{ route('galeri.index') }}" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition duration-300 {{ Route::is('galeri.*') || Request::is('admin/galeri*') ? 'bg-[#3e2723] text-white font-semibold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-medium' }}">
                        <i class="fa-regular fa-images w-5 text-center text-lg"></i> Galeri Foto
                    </a>
                    
                    {{-- 🛍️ INDUK MENU: PESANAN (WITH DROPDOWN) --}}
                    @php
                        $isPesananActive = Route::is('admin.po.*') || Request::is('admin/orders*') || Request::is('admin/laporan*');
                    @endphp
                    <div x-data="{ open: {{ $isPesananActive ? 'true' : 'false' }} }" class="space-y-1">
                        <!-- Induk Tombol Pesanan -->
                        <button type="button" 
                                @click="open = !open" 
                                class="w-full flex items-center justify-between px-4 py-3.5 rounded-2xl transition duration-300 text-left cursor-pointer select-none {{ $isPesananActive ? 'bg-[#3e2723]/10 font-bold text-[#3e2723]' : 'hover:bg-white/50 text-[#3e2723]/80 font-medium' }}">
                            <div class="flex items-center gap-4">
                                <i class="fa-solid fa-box-open w-5 text-center text-lg"></i> 
                                <span>Pesanan</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Sub-Menu Dropdown Accordion -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="pl-11 pr-2 space-y-1 pt-1"
                             style="display: none;">
                            
                            {{-- 📅 Sub-Menu 1: Jadwal PO (Dapur & Antrean) --}}
                            <a href="{{ route('admin.po.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition duration-200 {{ Route::is('admin.po.*') ? 'bg-[#3e2723] text-white font-semibold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-medium' }}">
                                <i class="fa-solid fa-calendar-days text-sm"></i> Order PaymentGateway
                            </a>

                            {{-- 💬 Sub-Menu 2: Order Manual (Transfer WhatsApp / Manual) --}}
                            <a href="{{ route('admin.orders.manual') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition duration-200 {{ Request::is('admin/orders/manual*') ? 'bg-[#3e2723] text-white font-semibold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-medium' }}">
                                <i class="fa-brands fa-whatsapp text-sm"></i> Order Manual
                            </a>

                            {{-- 📜 Sub-Menu 3: History / Riwayat Pesanan --}}
                            <a href="{{ route('admin.orders.history') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition duration-200 {{ Request::is('admin/orders/history*') ? 'bg-[#3e2723] text-white font-semibold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-medium' }}">
                                <i class="fa-solid fa-clock-rotate-left text-sm"></i> History Pesanan
                            </a>

                            {{-- 📊 Sub-Menu 4: Laporan Penjualan --}}
                            <a href="{{ route('admin.laporan.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition duration-200 {{ Request::is('admin/laporan*') ? 'bg-[#3e2723] text-white font-semibold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-medium' }}">
                                <i class="fa-solid fa-chart-line text-sm"></i> Laporan Penjualan
                            </a>
                        </div>
                    </div>

                    {{-- Super Admin Restricted Account Access --}}
                    @if(auth()->user()->role === 'super_admin')
                    <a href="{{ route('admin.users') }}" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition duration-300 {{ Route::is('admin.users') ? 'bg-[#3e2723] text-white font-semibold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-medium' }}">
                        <i class="fa-solid fa-users-gear w-5 text-center text-lg"></i> Manajemen Akun
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Footer Sidebar / User Info & Logout -->
            <div class="backdrop-blur-xl bg-white/30 border border-white/40 rounded-2xl p-4 flex items-center justify-between shadow-sm mt-4">
                <div class="flex items-center gap-3 min-w-0 flex-1 mr-2">
                    <div class="w-10 h-10 rounded-xl bg-[#3e2723] text-white flex items-center justify-center font-bold shrink-0 shadow-md">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider truncate">{{ auth()->user()->role ?? 'Admin' }}</p>
                        <p class="text-sm font-bold truncate text-[#3e2723]" title="{{ auth()->user()->name }}">{{ auth()->user()->name ?? 'Administrator' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="text-red-600 hover:scale-110 transition p-1" title="Log Keluar Sistem">
                        <i class="fa-solid fa-right-from-bracket text-lg"></i>
                    </button>
                </form>
            </div>
        </aside>

        {{-- CONTENT AREA --}}
        <main class="flex-1 flex flex-col gap-4">
            {{-- TOPBAR NAVBAR --}}
            <header class="w-full h-20 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] shadow-xl px-6 flex items-center justify-between">
                <h2 class="text-xl font-black tracking-tight text-[#3e2723]">@yield('page_title', 'Ringkasan Utama')</h2>
                <div class="text-sm font-semibold bg-white/60 border border-white/40 px-4 py-2 rounded-xl shadow-sm flex items-center gap-2">
                    <i class="fa-regular fa-calendar-days mr-1"></i> {{ date('d M Y') }}
                </div>
            </header>

            {{-- INDIVIDUAL PANEL CONTENT --}}
            <div class="flex-1 pb-10">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>