@extends('admin_layouts.master')

@section('page_title', 'Ringkasan Dashboard Utama')

@section('content')
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- BARIS TOMBOL AKSES PENJUNJUNG / VISITOR WEB --}}
    <div class="flex items-center justify-between backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-4 shadow-xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#3e2723] text-white flex items-center justify-center text-base shadow-md">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-[#3e2723]">Layanan Depan Toko (Visitor Web)</h4>
                <p class="text-xs text-gray-500">Lihat tampilan katalog menu dan halaman pemesanan pelanggan secara langsung.</p>
            </div>
        </div>
        
        {{-- 🔗 TOMBOL PINTAS KE HALAMAN UTAMA PELANGGAN --}}
        <a href="{{ url('/') }}" target="_blank" class="px-4 py-2.5 bg-[#3e2723] hover:bg-[#2c1b18] text-white text-xs font-bold rounded-xl shadow-lg transition duration-300 flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-globe"></i> Buka Website Visitor
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
        </a>
    </div>

    {{-- 1. KARTU STATISTIK KINERJA UTAMA (KPI CARDS) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Total Omzet Bulan Ini --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-[#3e2723]/60 uppercase tracking-wider">Omzet Bulan Ini</p>
                <h3 class="text-xl font-black text-[#3e2723] mt-1">
                    Rp {{ number_format($omzetBulanIni ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        {{-- Pesanan Masuk Hari Ini --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-blue-800/60 uppercase tracking-wider">Order Masuk Hari Ini</p>
                <h3 class="text-2xl font-black text-blue-900 mt-1">{{ $orderHariIniCount ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
        </div>

        {{-- Target Produksi Hari Ini --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-amber-800/60 uppercase tracking-wider">Jadwal PO Hari Ini</p>
                <h3 class="text-2xl font-black text-amber-900 mt-1">{{ $targetPOHariIniCount ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-cake-candles"></i>
            </div>
        </div>

        {{-- Belum Lunas / Sisa DP --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-rose-800/60 uppercase tracking-wider">Tagihan DP Belum Lunas</p>
                <h3 class="text-2xl font-black text-rose-900 mt-1">{{ $pendingDPCount ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    {{-- 2. TAMPILAN UTAMA (DUA KOLOM) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI (2 SPAN): TABEL PESANAN PERLU TINDAKAN CEPAT --}}
        <div class="lg:col-span-2 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-white/40">
                <div>
                    <h3 class="text-lg font-bold text-[#3e2723]">Pesanan Terbaru Membutuhkan Konfirmasi</h3>
                    <p class="text-xs text-gray-500">Pesanan PO yang belum dikonfirmasi atau perlu diperbarui.</p>
                </div>
                <a href="{{ route('admin.po.index') }}" class="text-xs font-bold text-[#3e2723] hover:underline flex items-center gap-1">
                    Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/40 text-[11px] font-bold uppercase tracking-wider text-[#3e2723]/70">
                            <th class="px-4 py-3">No. Order & Nama</th>
                            <th class="px-4 py-3 text-center">Tipe & Siap</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/30 text-xs font-medium">
                        @forelse($recentOrders ?? [] as $order)
                        @php
                            // Safely extract string value from Enum objects if present
                            $statusVal = is_object($order->status) ? ($order->status->value ?? (string) $order->status) : (string) $order->status;
                            $orderTypeVal = is_object($order->order_type) ? ($order->order_type->value ?? (string) $order->order_type) : (string) $order->order_type;
                        @endphp
                        <tr class="hover:bg-white/30 transition">
                            <td class="px-4 py-3">
                                <p class="font-bold text-[#3e2723]">{{ $order->order_number }}</p>
                                <p class="text-[11px] text-gray-600">{{ $order->customer_name }} ({{ $order->customer_phone }})</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $orderTypeVal === 'pickup' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ strtoupper($orderTypeVal) }}
                                </span>
                                <p class="text-[10px] text-gray-500 mt-1">
                                    {{ $order->fulfill_at ? \Carbon\Carbon::parse($order->fulfill_at)->format('d M, H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-xl text-[10px] font-bold shadow-sm {{ $statusVal === 'completed' ? 'bg-emerald-600 text-white' : ($statusVal === 'preparing' ? 'bg-blue-600 text-white' : 'bg-amber-500 text-white') }}">
                                    {{ ucfirst($statusVal) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-[#3e2723]">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                Belum ada pesanan terbaru hari ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KOLOM KANAN (1 SPAN): REKAP DAPUR HARI INI & AKSES CEPAT --}}
        <div class="space-y-6">
            
            {{-- Rekap Kebutuhan Dapur (Baking Sheet Summary) --}}
            <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 space-y-3">
                <h3 class="text-base font-bold text-[#3e2723] flex items-center gap-2">
                    <i class="fa-solid fa-fire-burner text-amber-700"></i> Target Pembuatan Kue Hari Ini
                </h3>
                <p class="text-xs text-gray-500">Agregasi total kue dari pesanan PO yang harus selesai hari ini.</p>

                <div class="space-y-2 pt-1">
                    @forelse($bakingItemsHariIni ?? [] as $item)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/40 border border-white/50 text-xs shadow-sm">
                        <span class="font-bold text-[#2d1f1b]">{{ $item->product_name }}</span>
                        <span class="px-2.5 py-1 rounded-lg bg-[#3e2723] text-white font-black">{{ $item->total_qty }} Pcs</span>
                    </div>
                    @empty
                    <div class="p-4 text-center text-xs text-gray-400 italic bg-white/20 rounded-xl border border-white/30">
                        Tidak ada target produksi kue untuk hari ini.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Pintasan Akses Cepat (Quick Actions) --}}
            <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 space-y-3">
                <h3 class="text-base font-bold text-[#3e2723]">Aksi Cepat</h3>
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('produk.index') }}" class="p-3 rounded-xl bg-white/60 hover:bg-white border border-white/50 shadow-sm flex flex-col items-center justify-center text-center transition">
                        <i class="fa-solid fa-plus text-base text-[#3e2723] mb-1"></i>
                        <span class="text-[10px] font-bold text-[#3e2723]">Tambah Menu</span>
                    </a>
                    <a href="{{ route('admin.po.index') }}" class="p-3 rounded-xl bg-white/60 hover:bg-white border border-white/50 shadow-sm flex flex-col items-center justify-center text-center transition">
                        <i class="fa-solid fa-calendar-days text-base text-[#3e2723] mb-1"></i>
                        <span class="text-[10px] font-bold text-[#3e2723]">Jadwal PO</span>
                    </a>
                    <a href="{{ url('/') }}" target="_blank" class="p-3 rounded-xl bg-[#3e2723]/10 hover:bg-[#3e2723]/20 border border-[#3e2723]/20 shadow-sm flex flex-col items-center justify-center text-center transition">
                        <i class="fa-solid fa-globe text-base text-[#3e2723] mb-1"></i>
                        <span class="text-[10px] font-bold text-[#3e2723]">Lihat Web</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection