@extends('admin_layouts.master')

@section('page_title', 'Order Manual & Konfirmasi WA')

@section('content')
<div x-data="{ 
    // State Modal Alamat
    showAddressModal: false, 
    modalCustomerName: '', 
    modalOrderNumber: '', 
    modalAddress: '',

    // State Modal Verifikasi Pembayaran
    showVerifyModal: false,
    verifyFormAction: '',
    verifyOrderNumber: '',

    openAddressModal(name, number, address) {
        this.modalCustomerName = name;
        this.modalOrderNumber = number;
        this.modalAddress = address;
        this.showAddressModal = true;
    },

    openVerifyModal(actionUrl, number) {
        this.verifyFormAction = actionUrl;
        this.verifyOrderNumber = number;
        this.showVerifyModal = true;
    }
}" class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- ALERT SUCCESS FLASHDATA --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-900 text-xs font-bold flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button @click="show = false" class="text-emerald-700 hover:text-emerald-950">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    {{-- 1. STATISTIK RINGKAS ORDER MANUAL --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Menunggu Verifikasi --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-900/60 uppercase tracking-wider">Perlu Dikonfirmasi</p>
                <h3 class="text-2xl font-black text-amber-950 mt-1">
                    {{ $orders->where('payment_status', 'unpaid')->count() }} Transaksi
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        {{-- Sudah Terverifikasi --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-800/60 uppercase tracking-wider">Terverifikasi (DP / Paid)</p>
                <h3 class="text-2xl font-black text-emerald-900 mt-1">
                    {{ $orders->whereIn('payment_status', ['paid', 'dp', 'partial'])->count() }} Transaksi
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        {{-- Total Transaksi Manual --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Total Order Manual</p>
                <h3 class="text-2xl font-black text-[#3e2723] mt-1">
                    {{ $orders->count() }} Transaksi
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-xl shadow-sm">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
        </div>
    </div>

    {{-- 2. BAR PENCARIAN & FILTER --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 flex flex-col md:flex-row gap-4 items-center justify-between">
        <form method="GET" action="{{ route('admin.orders.manual') }}" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Order, Nama, atau No. WhatsApp..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
            </div>

            <select name="status_bayar" class="px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                <option value="">Semua Status Bayar</option>
                <option value="unpaid" {{ request('status_bayar') == 'unpaid' ? 'selected' : '' }}>Belum Lunas (Unpaid)</option>
                <option value="dp" {{ request('status_bayar') == 'dp' ? 'selected' : '' }}>Uang Muka (DP 50%)</option>
                <option value="paid" {{ request('status_bayar') == 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
            </select>

            <button type="submit" class="px-5 py-2.5 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            
            @if(request()->hasAny(['search', 'status_bayar']))
            <a href="{{ route('admin.orders.manual') }}" class="px-4 py-2.5 bg-white/60 text-[#3e2723] text-xs font-bold rounded-xl border border-white/50 hover:bg-white transition flex items-center justify-center">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- 3. TABEL DATA ORDER MANUAL --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden space-y-4">
        <h3 class="text-base font-bold text-[#3e2723] flex items-center gap-2">
            <i class="fa-brands fa-whatsapp text-emerald-600 text-lg"></i> Daftar Pesanan Transfer Manual (WhatsApp)
        </h3>

        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-4 py-3.5">No. Order & Pelanggan</th>
                        <th class="px-4 py-3.5">Pengambilan & Waktu</th>
                        <th class="px-4 py-3.5">Item Pesanan</th>
                        <th class="px-4 py-3.5 text-center">Status Pesanan</th>
                        <th class="px-4 py-3.5 text-center">Skema & Total</th>
                        <th class="px-4 py-3.5 text-center">Status Bayar</th>
                        <th class="px-4 py-3.5 text-center">Aksi / Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-xs font-medium">
                    @forelse($orders as $order)
                    @php
                        $paymentStatusVal = is_object($order->payment_status) ? ($order->payment_status->value ?? (string) $order->payment_status) : (string) $order->payment_status;
                        $paymentPlanVal   = is_object($order->payment_plan) ? ($order->payment_plan->value ?? (string) $order->payment_plan) : (string) $order->payment_plan;
                        $orderTypeVal     = is_object($order->order_type) ? ($order->order_type->value ?? (string) $order->order_type) : (string) $order->order_type;
                        $orderStatusVal   = is_object($order->status) ? ($order->status->value ?? (string) $order->status) : (string) $order->status;

                        // Sanitasi nomor HP untuk WhatsApp Web link
                        $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                    @endphp
                    <tr class="hover:bg-white/30 transition">
                        {{-- NO ORDER & PELANGGAN --}}
                        <td class="px-4 py-3.5">
                            <p class="font-black text-[#3e2723]">{{ $order->order_number }}</p>
                            <p class="font-bold text-[#3e2723]/90 mt-0.5">{{ $order->customer_name }}</p>
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline mt-0.5">
                                <i class="fa-brands fa-whatsapp"></i> {{ $order->customer_phone }}
                            </a>
                        </td>

                        {{-- TIPE PENGAMBILAN & WAKTU SIAP --}}
                        <td class="px-4 py-3.5">
                            @if($orderTypeVal === 'delivery')
                                <button type="button" 
                                    @click="openAddressModal('{{ addslashes($order->customer_name) }}', '{{ $order->order_number }}', '{{ addslashes($order->delivery_address ?? 'Alamat tidak dicantumkan') }}')"
                                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800 hover:bg-blue-200 transition flex items-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-truck text-[9px]"></i> DELIVERY (Cek Alamat)
                                </button>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800 inline-block">
                                    <i class="fa-solid fa-store text-[9px]"></i> PICKUP
                                </span>
                            @endif

                            <p class="font-bold text-[#3e2723] mt-1.5">
                                {{ $order->fulfill_at ? \Carbon\Carbon::parse($order->fulfill_at)->translatedFormat('d M Y, H:i') : '-' }} WIB
                            </p>
                        </td>

                        {{-- LIST ITEM PESANAN --}}
                        <td class="px-4 py-3.5">
                            <ul class="space-y-0.5">
                                @foreach($order->items as $item)
                                <li class="text-[11px] text-[#3e2723]/90">
                                    <strong class="text-[#3e2723]">{{ $item->quantity }}x</strong> {{ $item->product_name }}
                                </li>
                                @endforeach
                            </ul>
                            @if($order->notes)
                            <p class="text-[10px] italic text-amber-900 mt-1 bg-amber-50/60 p-1 rounded border border-amber-200/50">
                                Note: "{{ $order->notes }}"
                            </p>
                            @endif
                        </td>

                        {{-- DROPDOWN STATUS PESANAN (PRODUKSI) --}}
                        <td class="px-4 py-3.5 text-center">
                            <form action="{{ route('admin.po.updateStatus', $order->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 text-[11px] font-bold rounded-xl border border-white/50 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-sm cursor-pointer
                                    {{ $orderStatusVal === 'completed' ? 'bg-emerald-600 text-white' : '' }}
                                    {{ $orderStatusVal === 'ready' ? 'bg-teal-600 text-white' : '' }}
                                    {{ $orderStatusVal === 'preparing' ? 'bg-blue-600 text-white' : '' }}
                                    {{ $orderStatusVal === 'confirmed' ? 'bg-indigo-600 text-white' : '' }}
                                    {{ $orderStatusVal === 'pending' ? 'bg-amber-500 text-white' : '' }}
                                    {{ $orderStatusVal === 'cancelled' ? 'bg-rose-600 text-white' : '' }}">
                                    <option value="pending" {{ $orderStatusVal === 'pending' ? 'selected' : '' }} class="bg-white text-gray-800">Menunggu (Pending)</option>
                                    <option value="confirmed" {{ $orderStatusVal === 'confirmed' ? 'selected' : '' }} class="bg-white text-gray-800">Dikonfirmasi (Confirmed)</option>
                                    <option value="preparing" {{ $orderStatusVal === 'preparing' ? 'selected' : '' }} class="bg-white text-gray-800">Diproses (Preparing)</option>
                                    <option value="ready" {{ $orderStatusVal === 'ready' ? 'selected' : '' }} class="bg-white text-gray-800">Siap diambil/dikirim (Ready)</option>
                                    <option value="completed" {{ $orderStatusVal === 'completed' ? 'selected' : '' }} class="bg-white text-gray-800">Selesai (Completed)</option>
                                    <option value="cancelled" {{ $orderStatusVal === 'cancelled' ? 'selected' : '' }} class="bg-white text-gray-800">Dibatalkan (Cancelled)</option>
                                </select>
                            </form>
                        </td>

                        {{-- SKEMA PEMBAYARAN --}}
                        <td class="px-4 py-3.5 text-center">
                            <p class="font-black text-[#3e2723]">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </p>
                            @if(strtolower($paymentPlanVal) === 'dp')
                                <span class="text-[10px] text-amber-800 font-bold bg-amber-100 px-2 py-0.5 rounded-full inline-block mt-0.5">
                                    DP 50%: Rp {{ number_format($order->dp_amount ?? ($order->total_amount * 0.5), 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-[10px] text-emerald-800 font-bold bg-emerald-100 px-2 py-0.5 rounded-full inline-block mt-0.5">
                                    Full Payment
                                </span>
                            @endif
                        </td>

                        {{-- STATUS PEMBAYARAN --}}
                        <td class="px-4 py-3.5 text-center">
                            @if(in_array(strtolower($paymentStatusVal), ['paid', 'lunas']))
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-emerald-600 text-white shadow-sm inline-block">
                                    LUNAS
                                </span>
                            @elseif(in_array(strtolower($paymentStatusVal), ['dp', 'partial']))
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-amber-500 text-white shadow-sm inline-block">
                                    DP DITERIMA
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-600 text-white shadow-sm inline-block">
                                    BELUM BAYAR
                                </span>
                            @endif
                        </td>

                        {{-- AKSI VERIFIKASI / CHAT WA --}}
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Tombol Pemicu Modal Verifikasi --}}
                                <button type="button" 
                                    @click="openVerifyModal('{{ route('admin.orders.verifyPayment', $order->id) }}', '{{ $order->order_number }}')"
                                    class="p-2 bg-emerald-700 text-white rounded-xl shadow hover:bg-emerald-800 transition text-xs font-bold flex items-center gap-1"
                                    title="Tandai Pembayaran Lunas/DP">
                                    <i class="fa-solid fa-check"></i> Verifikasi
                                </button>

                                {{-- Tombol Direct Chat WA --}}
                                @php
                                    $waMsg = "Halo Kak {$order->customer_name}, kami dari Admin Edelweiss Bakery ingin mengonfirmasi pesanan No. *{$order->order_number}*. Apakah bukti transfer sudah dikirimkan?";
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waMsg) }}" target="_blank" class="p-2 bg-emerald-500 text-white rounded-xl shadow hover:bg-emerald-600 transition text-xs font-bold" title="Chat WA Pelanggan">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">
                            Belum ada transaksi pesanan manual via WhatsApp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 1: ALAMAT LENGKAP PENGIRIMAN (DELIVERY) --}}
    {{-- ========================================================================= --}}
    <div x-show="showAddressModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.away="showAddressModal = false" class="bg-white/90 backdrop-blur-2xl border border-white/80 rounded-[2rem] p-6 max-w-md w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3 border-gray-200">
                <div class="flex items-center gap-2 text-blue-800">
                    <i class="fa-solid fa-truck-ramp-box text-xl"></i>
                    <h3 class="font-black text-base">Detail Alamat Pengiriman</h3>
                </div>
                <button @click="showAddressModal = false" class="text-gray-400 hover:text-gray-700">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <div>
                    <p class="text-gray-500 font-medium">No. Order / Pelanggan:</p>
                    <p class="font-bold text-[#3e2723]" x-text="modalOrderNumber + ' - ' + modalCustomerName"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Alamat Lengkap:</p>
                    <div class="p-3 bg-blue-50/70 border border-blue-200/60 rounded-xl font-semibold text-gray-800 leading-relaxed mt-1" x-text="modalAddress"></div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button @click="showAddressModal = false" type="button" class="px-5 py-2.5 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow hover:bg-[#2c1b18] transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2: KONFIRMASI VERIFIKASI PEMBAYARAN --}}
    {{-- ========================================================================= --}}
    <div x-show="showVerifyModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.away="showVerifyModal = false" class="bg-white/90 backdrop-blur-2xl border border-white/80 rounded-[2rem] p-6 max-w-md w-full shadow-2xl text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fa-solid fa-file-circle-check"></i>
            </div>

            <div>
                <h3 class="font-black text-lg text-[#3e2723]">Konfirmasi Verifikasi</h3>
                <p class="text-xs text-gray-600 mt-1">
                    Apakah Anda telah memeriksa mutasi rekening dan ingin menyetujui pembayaran order <strong class="text-[#3e2723]" x-text="verifyOrderNumber"></strong>?
                </p>
            </div>

            <form :action="verifyFormAction" method="POST" class="pt-2 flex gap-3 justify-center">
                @csrf
                @method('PATCH')
                <button type="button" @click="showVerifyModal = false" class="px-5 py-2.5 bg-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-300 transition w-1/2">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition w-1/2">
                    Ya, Verifikasi
                </button>
            </form>
        </div>
    </div>

</div>
@endsection