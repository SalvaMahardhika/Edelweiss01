@extends('admin_layouts.master')

@section('page_title', 'Jadwal & Produksi Pre-Order (PO)')

@section('content')
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- 1. RINGKASAN AMBIEN STATISTIK PO --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Jadwal PO Hari Ini</p>
                <h3 class="text-2xl font-black text-[#3e2723] mt-1">{{ $todayPO }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-clock font-bold"></i>
            </div>
        </div>

        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-800/60 uppercase tracking-wider">Menunggu Konfirmasi</p>
                <h3 class="text-2xl font-black text-amber-900 mt-1">{{ $pendingPO }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-blue-800/60 uppercase tracking-wider">Sedang Diproduksi Dapur</p>
                <h3 class="text-2xl font-black text-blue-900 mt-1">{{ $preparingPO }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-fire-burner"></i>
            </div>
        </div>
    </div>

    {{-- 2. HEADER & FILTER JADWAL --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <form method="GET" action="{{ route('admin.po.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Pengambilan / Kirim</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Produksi</label>
                <select name="status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Preparing (Dipanggang)</option>
                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready (Siap)</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Pembayaran</label>
                <select name="payment_status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Pembayaran</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>DP (Sebagian)</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.po.index') }}" class="py-2 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- 3. TABEL UTAMA MANAJEMEN PO --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4">No. Order & Pelanggan</th>
                        <th class="px-6 py-4">Item Pesanan (Kue)</th>
                        <th class="px-6 py-4 text-center">Jadwal Siap (`fulfill_at`)</th>
                        <th class="px-6 py-4 text-center">Pembayaran</th>
                        <th class="px-6 py-4 text-center">Status Produksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    @forelse($orders as $order)
                    @php
                        $orderTypeVal = is_object($order->order_type) ? $order->order_type->value : $order->order_type;
                        $statusVal = is_object($order->status) ? $order->status->value : $order->status;
                        $paymentStatusVal = is_object($order->payment_status) ? $order->payment_status->value : $order->payment_status;
                    @endphp
                    <tr class="hover:bg-white/30 transition">
                        
                        {{-- No Order & Info Pembeli --}}
                        <td class="px-6 py-4">
                            <p class="font-black text-[#3e2723]">{{ $order->order_number }}</p>
                            <p class="text-xs font-bold text-gray-700 mt-0.5">{{ $order->customer_name }}</p>
                            <p class="text-[11px] text-gray-500"><i class="fa-solid fa-phone text-[9px] mr-1"></i>{{ $order->customer_phone }}</p>
                            
                            {{-- Badge Mode Order & Tombol Modal Alamat --}}
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold {{ $orderTypeVal === 'pickup' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    <i class="fa-solid {{ $orderTypeVal === 'pickup' ? 'fa-store' : 'fa-truck' }} mr-1"></i> {{ strtoupper($orderTypeVal) }}
                                </span>

                                @if($orderTypeVal === 'delivery')
                                    <button type="button" 
                                            onclick="openAddressModal('{{ $order->order_number }}', '{{ e($order->customer_name) }}', '{{ e($order->delivery_address) }}')" 
                                            class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-white/80 hover:bg-blue-600 hover:text-white px-2 py-0.5 rounded-md border border-blue-200 shadow-sm transition">
                                        <i class="fa-solid fa-location-dot text-[9px]"></i> Lihat Alamat
                                    </button>
                                @endif
                            </div>
                        </td>

                        {{-- Item Kue & Catatan --}}
                        <td class="px-6 py-4">
                            <ul class="space-y-1 text-xs">
                                @foreach($order->items as $item)
                                    <li class="text-[#2d1f1b]">
                                        <span class="font-bold text-[#3e2723]">{{ $item->quantity }}x</span> {{ $item->product_name }}
                                    </li>
                                @endforeach
                            </ul>
                            @if($order->notes)
                                <p class="text-[10px] text-amber-900 bg-amber-500/10 p-1.5 rounded-lg mt-2 italic border border-amber-500/20">
                                    <strong>Ket:</strong> "{{ $order->notes }}"
                                </p>
                            @endif
                        </td>

                        {{-- Tanggal SIAP (Fulfill At) --}}
                        <td class="px-6 py-4 text-center">
                            @if($order->fulfill_at)
                                <p class="font-bold text-xs text-[#3e2723]">{{ \Carbon\Carbon::parse($order->fulfill_at)->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] font-semibold text-gray-500">{{ \Carbon\Carbon::parse($order->fulfill_at)->format('H:i') }} WIB</p>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum Diset</span>
                            @endif
                        </td>

                        {{-- Status Pembayaran & Sisa DP --}}
                        <td class="px-6 py-4 text-center">
                            <form method="POST" action="{{ route('admin.po.updatePayment', $order->id) }}">
                                @csrf
                                @method('PATCH')
                                <select name="payment_status" onchange="this.form.submit()" class="text-xs font-bold px-2 py-1 rounded-xl border border-white/40 shadow-sm cursor-pointer {{ $paymentStatusVal === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($paymentStatusVal === 'partial' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    <option value="unpaid" {{ $paymentStatusVal === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                                    <option value="partial" {{ $paymentStatusVal === 'partial' ? 'selected' : '' }}>DP (Sebagian)</option>
                                    <option value="paid" {{ $paymentStatusVal === 'paid' ? 'selected' : '' }}>Lunas</option>
                                </select>
                            </form>
                            <p class="text-[11px] font-bold text-[#3e2723] mt-1">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        </td>

                        {{-- Status Pengerjaan (Dapur/PO) --}}
                        <td class="px-6 py-4 text-center">
                            <form method="POST" action="{{ route('admin.po.updateStatus', $order->id) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-xs font-bold px-3 py-1.5 rounded-xl border border-white/50 shadow-md cursor-pointer transition focus:outline-none {{ $statusVal === 'completed' ? 'bg-emerald-600 text-white' : ($statusVal === 'preparing' ? 'bg-blue-600 text-white' : ($statusVal === 'ready' ? 'bg-purple-600 text-white' : 'bg-amber-500 text-white')) }}">
                                    <option value="pending" {{ $statusVal === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $statusVal === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="preparing" {{ $statusVal === 'preparing' ? 'selected' : '' }}>Preparing (Dipanggang)</option>
                                    <option value="ready" {{ $statusVal === 'ready' ? 'selected' : '' }}>Ready (Siap Ambil/Kirim)</option>
                                    <option value="completed" {{ $statusVal === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                                    <option value="cancelled" {{ $statusVal === 'cancelled' ? 'selected' : '' }}>Batal</option>
                                </select>
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                            <i class="fa-solid fa-calendar-xmark text-2xl mb-2 text-gray-400 block"></i>
                            Tidak ada jadwal pesanan PO yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>

{{-- 🚚 MODAL POPUP: ALAMAT PENGIRIMAN --}}
<div id="addressModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-md p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl relative space-y-4 my-auto">
        <div class="flex justify-between items-center pb-2 border-b border-[#3e2723]/15">
            <h3 class="text-base font-bold text-[#3e2723] flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-blue-700"></i> Alamat Pengiriman
            </h3>
            <button type="button" onclick="closeAddressModal()" class="w-7 h-7 rounded-full bg-white/40 flex items-center justify-center font-bold text-[#3e2723] hover:bg-white/80 transition">✕</button>
        </div>

        <div class="space-y-3">
            <div>
                <p class="text-[10px] font-bold uppercase text-gray-500 tracking-wider">No. Pesanan & Pelanggan</p>
                <p id="modalOrderInfo" class="text-sm font-black text-[#3e2723] mt-0.5">-</p>
            </div>

            <div>
                <p class="text-[10px] font-bold uppercase text-gray-500 tracking-wider">Alamat Lengkap</p>
                <div class="mt-1 p-3.5 bg-white/70 border border-white rounded-xl text-xs font-semibold text-[#2d1f1b] shadow-inner leading-relaxed min-h-[5rem] whitespace-pre-line" id="modalAddressText">
                    -
                </div>
            </div>
        </div>

        <div class="pt-2">
            <button type="button" onclick="closeAddressModal()" class="w-full py-2.5 text-xs font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-md">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openAddressModal(orderNumber, customerName, address) {
        document.getElementById('modalOrderInfo').innerText = `${orderNumber} - ${customerName}`;
        document.getElementById('modalAddressText').innerText = address && address.trim() !== '' ? address : 'Alamat pengiriman belum diisi / tidak tersedia.';
        document.getElementById('addressModal').classList.remove('hidden');
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
    }
</script>
@endsection