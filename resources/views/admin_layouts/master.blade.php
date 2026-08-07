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
    
    {{-- 🔴 JQUERY & DATATABLES DEPENDENCIES (DIBUTUHKAN UNTUK SEMUA TABEL) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        /* Custom Styling agar DataTables menyatu dengan Glassmorphism Tailwind */
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter, 
        .dataTables_wrapper .dataTables_info, 
        .dataTables_wrapper .dataTables_processing, 
        .dataTables_wrapper .dataTables_paginate {
            color: #3e2723 !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }
        table.dataTable tbody tr {
            background-color: transparent !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3e2723 !important;
            color: white !important;
            border-radius: 0.5rem !important;
            border: none !important;
        }
    </style>
</head>
<body class="bg-[#fafafa] text-[#3e2723] min-h-screen relative overflow-x-hidden">

    {{-- BACKGROUND ART & BLUR GLOW --}}
    <div class="fixed inset-0 z-0 pointer-events-none">
        <img src="{{ asset('img/dashboard/assets/3.png') }}" class="w-full h-full object-cover opacity-15 filter grayscale" onerror="this.style.display='none'">
        <div class="absolute inset-0 bg-gradient-to-br from-[#fafafa] via-[#f5efe8] to-[#ede5dc]/90"></div>
        
        <!-- Ambient Liquid Glow Tokens -->
        <div class="absolute top-10 left-10 w-96 h-96 bg-[#d4af37] rounded-full blur-[120px] opacity-15"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-[#3e2723] rounded-full blur-[120px] opacity-10"></div>
    </div>

    {{-- MAIN WRAPPER INTERFACE WITH ALPINEJS STATE FOR SIDEBAR --}}
    <div x-data="{ sidebarOpen: true }" class="relative z-10 flex min-h-screen p-4 gap-4 items-start">
        
        {{-- BACKDROP UNTUK TAMPILAN MOBILE --}}
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/30 backdrop-blur-sm z-30 lg:hidden"
             style="display: none;"></div>

        {{-- SIDEBAR CONTAINER (PATEN & INDEPENDENT SCROLL) --}}
        <aside x-show="sidebarOpen"
               x-transition:enter="transition-all ease-out duration-300"
               x-transition:enter-start="-translate-x-full opacity-0 max-w-0 p-0 overflow-hidden"
               x-transition:enter-end="translate-x-0 opacity-100 max-w-[18rem]"
               x-transition:leave="transition-all ease-in duration-300"
               x-transition:leave-start="translate-x-0 opacity-100 max-w-[18rem]"
               x-transition:leave-end="-translate-x-full opacity-0 max-w-0 p-0 overflow-hidden"
               class="w-72 max-w-[18rem] backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-2xl p-5 flex flex-col shrink-0 fixed lg:sticky top-4 z-40 h-[calc(100vh-2rem)] overflow-hidden">
            
            <!-- Branding Header -->
            <div class="flex items-center justify-between px-2 mb-6 shrink-0">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo/logo2.png') }}" class="h-9 w-auto drop-shadow-md">
                    <span class="font-black text-base tracking-wider bg-gradient-to-r from-[#3e2723] to-[#b8860b] bg-clip-text text-transparent">ADMIN DASHBOARD</span>
                </div>
                <!-- Tombol Tutup Sidebar untuk Mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-[#3e2723]">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <!-- Navigation Links (Scrollable internal tersendiri) -->
            <nav class="space-y-1.5 overflow-y-auto pr-1 flex-1 min-h-0">
                {{-- Dashboard Summary Link --}}
                <a href="{{ route('admin.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition duration-300 text-xs {{ Route::is('admin.index') ? 'bg-[#3e2723] text-white font-bold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-semibold' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i> Dashboard
                </a>
                
                {{-- 🏷️ KATEGORI MENU --}}
                <a href="{{ route('kategori.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition duration-300 text-xs {{ Route::is('kategori.*') ? 'bg-[#3e2723] text-white font-bold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-semibold' }}">
                    <i class="fa-solid fa-tags w-5 text-center text-base"></i> Kategori Menu
                </a>

                {{-- 📦 MANAJEMEN MENU --}}
                <a href="{{ route('produk.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition duration-300 text-xs {{ Route::is('produk.*') || Request::is('admin/menu*') ? 'bg-[#3e2723] text-white font-bold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-semibold' }}">
                    <i class="fa-solid fa-layer-group w-5 text-center text-base"></i> Manajemen Menu
                </a>

                {{-- 🖼️ GALERI FOTO --}}
                <a href="{{ route('galeri.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition duration-300 text-xs {{ Route::is('galeri.*') || Request::is('admin/galeri*') ? 'bg-[#3e2723] text-white font-bold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-semibold' }}">
                    <i class="fa-regular fa-images w-5 text-center text-base"></i> Galeri Foto
                </a>
                
                {{-- 🔒 LOCK TANGGAL / KUOTA LIBUR --}}
                <a href="{{ route('admin.disabled_dates.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition duration-300 text-xs {{ Route::is('admin.disabled_dates.*') || Request::is('admin/disabled-dates*') ? 'bg-[#3e2723] text-white font-bold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-semibold' }}">
                    <i class="fa-solid fa-calendar-xmark w-5 text-center text-base"></i> Lock Tanggal
                </a>

                {{-- 🛍️ INDUK MENU: PESANAN (WITH DROPDOWN) --}}
                @php
                    $isPesananActive = Route::is('admin.po.*') || Request::is('admin/orders*') || Request::is('admin/laporan*');
                @endphp
                <div x-data="{ open: {{ $isPesananActive ? 'true' : 'false' }} }" class="space-y-1">
                    <!-- Induk Tombol Pesanan -->
                    <button type="button" 
                            @click="open = !open" 
                            class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl transition duration-300 text-xs text-left cursor-pointer select-none {{ $isPesananActive ? 'bg-[#3e2723]/10 font-black text-[#3e2723]' : 'hover:bg-white/50 text-[#3e2723]/80 font-semibold' }}">
                        <div class="flex items-center gap-3.5">
                            <i class="fa-solid fa-box-open w-5 text-center text-base"></i> 
                            <span>Pesanan</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Sub-Menu Dropdown Accordion -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="pl-9 pr-1 space-y-1 pt-1"
                         style="display: none;">
                        
                        {{-- 📅 Sub-Menu 1: Order PaymentGateway --}}
                        <a href="{{ route('admin.po.index') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[11px] transition duration-200 {{ Route::is('admin.po.*') ? 'bg-[#3e2723] text-white font-bold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-semibold' }}">
                            <i class="fa-solid fa-calendar-days text-xs"></i> Order PaymentGateway
                        </a>

                        {{-- 💬 Sub-Menu 2: Order Manual --}}
                        <a href="{{ route('admin.orders.manual') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[11px] transition duration-200 {{ Request::is('admin/orders/manual*') ? 'bg-[#3e2723] text-white font-bold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-semibold' }}">
                            <i class="fa-brands fa-whatsapp text-xs"></i> Order Manual
                        </a>

                        {{-- 📜 Sub-Menu 3: History Pesanan --}}
                        <a href="{{ route('admin.orders.history') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[11px] transition duration-200 {{ Request::is('admin/orders/history*') ? 'bg-[#3e2723] text-white font-bold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-semibold' }}">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i> History Pesanan
                        </a>

                        {{-- 📊 Sub-Menu 4: Laporan Penjualan --}}
                        <a href="{{ route('admin.laporan.index') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[11px] transition duration-200 {{ Request::is('admin/laporan*') ? 'bg-[#3e2723] text-white font-bold shadow-md' : 'text-[#3e2723]/70 hover:bg-white/60 hover:text-[#3e2723] font-semibold' }}">
                            <i class="fa-solid fa-chart-line text-xs"></i> Laporan Penjualan
                        </a>
                    </div>
                </div>

                {{-- Super Admin Restricted Account Access --}}
                @if(auth()->check() && auth()->user()->role === 'super_admin')
                <a href="{{ route('admin.users') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition duration-300 text-xs {{ Route::is('admin.users') ? 'bg-[#3e2723] text-white font-bold shadow-lg' : 'hover:bg-white/50 text-[#3e2723]/80 font-semibold' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center text-base"></i> Manajemen Akun
                </a>
                @endif
            </nav>

            <!-- Footer Sidebar / User Info & Logout (Langsung Menempel di Bawah Menu) -->
            <div class="backdrop-blur-xl bg-white/50 border border-white/60 rounded-2xl p-3 flex items-center justify-between shadow-sm mt-auto shrink-0 pt-3">
                <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                    <div class="w-9 h-9 rounded-xl bg-[#3e2723] text-white flex items-center justify-center font-black text-xs shrink-0 shadow-md">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wider truncate">{{ auth()->user()->role ?? 'Admin' }}</p>
                        <p class="text-xs font-black truncate text-[#3e2723]" title="{{ auth()->user()->name ?? 'Admin' }}">{{ auth()->user()->name ?? 'Administrator' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="text-red-600 hover:scale-110 transition p-1.5 rounded-lg hover:bg-red-50" title="Log Keluar Sistem">
                        <i class="fa-solid fa-right-from-bracket text-base"></i>
                    </button>
                </form>
            </div>
        </aside>

        {{-- CONTENT AREA --}}
        <main class="flex-1 min-w-0 flex flex-col gap-4 transition-all duration-300">
            {{-- TOPBAR NAVBAR --}}
            <header class="w-full h-20 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] shadow-xl px-6 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-4">
                    {{-- TOMBOL TOGGLE SIDEBAR (HIDE / SHOW) --}}
                    <button @click="sidebarOpen = !sidebarOpen" 
                            type="button" 
                            class="w-10 h-10 rounded-xl bg-white/60 border border-white/40 hover:bg-[#3e2723] hover:text-white text-[#3e2723] flex items-center justify-center transition shadow-sm"
                            title="Toggle Sidebar">
                        <i class="fa-solid" :class="sidebarOpen ? 'fa-indent' : 'fa-outdent'"></i>
                    </button>

                    <h2 class="text-xl font-black tracking-tight text-[#3e2723] truncate">@yield('page_title', 'Ringkasan Utama')</h2>
                </div>

                <div class="text-sm font-semibold bg-white/60 border border-white/40 px-4 py-2 rounded-xl shadow-sm flex items-center gap-2 shrink-0">
                    <i class="fa-regular fa-calendar-days mr-1"></i> {{ date('d M Y') }}
                </div>
            </header>

            {{-- INDIVIDUAL PANEL CONTENT --}}
            <div class="flex-1 pb-10 min-w-0">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>