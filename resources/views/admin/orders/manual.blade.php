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

    // 🖼️ State Modal Bukti Transfer
    showProofModal: false,
    modalProofUrl: '',
    proofOrderNumber: '',
    proofHistoryList: [],

    // 📜 State Sub-Modal Riwayat Foto Bukti
    showHistoryModal: false,
    selectedHistoryImgUrl: '',

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
    },

    openProofModal(url, number, historyList = []) {
        this.modalProofUrl = url;
        this.proofOrderNumber = number;
        this.proofHistoryList = historyList;
        this.showProofModal = true;
    },

    openHistoryModal() {
        this.selectedHistoryImgUrl = this.modalProofUrl;
        this.showHistoryModal = true;
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

            <select name="has_proof" class="px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                <option value="">Semua Bukti TF</option>
                <option value="1" {{ request('has_proof') == '1' ? 'selected' : '' }}>Ada Bukti TF</option>
                <option value="0" {{ request('has_proof') == '0' ? 'selected' : '' }}>Belum Ada Bukti</option>
            </select>

            <button type="submit" class="px-5 py-2.5 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            
            @if(request()->hasAny(['search', 'status_bayar', 'has_proof']))
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

                        $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }

                        // 🟢 MENGAMBIL DATA RIWAYAT BUKTI DARI MODEL ACCESSOR SECARA OTOMATIS
                        $historyFormatted = $order->payment_proof_history ?? [];
                        $latestProofUrl   = $order->latest_payment_proof_url;
                        $hasProof         = !empty($latestProofUrl);
                    @endphp
                    <tr class="hover:bg-white/30 transition">
                        <td class="px-4 py-3.5">
                            <p class="font-black text-[#3e2723]">{{ $order->order_number }}</p>
                            <p class="font-bold text-[#3e2723]/90 mt-0.5">{{ $order->customer_name }}</p>
                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline mt-0.5">
                                <i class="fa-brands fa-whatsapp"></i> {{ $order->customer_phone }}
                            </a>
                            <p class="text-[10px] font-semibold text-gray-500 mt-1 flex items-center gap-1">
                                <i class="fa-regular fa-clock text-amber-700"></i>
                                Dibuat: {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }} WIB
                            </p>
                        </td>

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

                        {{-- STATUS PRODUKSI (INTERCEPT DENGAN MODAL KONFIRMASI) --}}
                        <td class="px-3 py-4 text-center">
                            <form id="status-form-{{ $order->id }}" method="POST" action="{{ route('admin.po.updateStatus', $order->id) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" 
                                        onchange="handleStatusChange(this, '{{ $order->id }}', '{{ $order->order_number }}', '{{ $orderStatusVal }}')" 
                                        class="w-full text-xs font-bold px-2 py-1.5 rounded-xl border border-white/50 shadow-md cursor-pointer transition focus:outline-none {{ $orderStatusVal === 'completed' ? 'bg-emerald-600 text-white' : ($orderStatusVal === 'preparing' ? 'bg-blue-600 text-white' : ($orderStatusVal === 'ready' ? 'bg-purple-600 text-white' : ($orderStatusVal === 'confirmed' ? 'bg-indigo-600 text-white' : ($orderStatusVal === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white')))) }}">
                                    <option value="pending" class="bg-white text-gray-800" {{ $orderStatusVal === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" class="bg-white text-gray-800" {{ $orderStatusVal === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="preparing" class="bg-white text-gray-800" {{ $orderStatusVal === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                    <option value="ready" class="bg-white text-gray-800" {{ $orderStatusVal === 'ready' ? 'selected' : '' }}>Ready</option>
                                    <option value="completed" class="bg-white text-gray-800" {{ $orderStatusVal === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                                    <option value="cancelled" class="bg-white text-gray-800" {{ $orderStatusVal === 'cancelled' ? 'selected' : '' }}>Batal</option>
                                </select>
                            </form>
                        </td>

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

                        <td class="px-4 py-3.5 text-center space-y-1.5">
                            <div>
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
                            </div>

                            <div>
                                @if($hasProof)
                                    <button type="button" 
                                            @click="openProofModal('{{ $latestProofUrl }}', '{{ $order->order_number }}', {{ json_encode($historyFormatted) }})"
                                            class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100/80 hover:bg-emerald-200 border border-emerald-300 px-2 py-1 rounded-lg shadow-sm transition">
                                        <i class="fa-solid fa-image text-emerald-700"></i> Cek Bukti TF
                                        @if(count($historyFormatted) > 1)
                                            <span class="bg-amber-600 text-white text-[9px] px-1.5 py-0.2 rounded-full font-extrabold ml-0.5" title="Customer sudah ganti gambar {{ count($historyFormatted) }}x">
                                                {{ count($historyFormatted) }}x Upload
                                            </span>
                                        @endif
                                    </button>
                                @else
                                    <span class="text-[10px] text-gray-400 italic block">Belum ada bukti</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" 
                                    @click="openVerifyModal('{{ route('admin.orders.verifyPayment', $order->id) }}', '{{ $order->order_number }}')"
                                    class="p-2 bg-emerald-700 text-white rounded-xl shadow hover:bg-emerald-800 transition text-xs font-bold flex items-center gap-1"
                                    title="Tandai Pembayaran Lunas/DP">
                                    <i class="fa-solid fa-check"></i> Verifikasi
                                </button>

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

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>

    {{-- MODAL 1: ALAMAT LENGKAP PENGIRIMAN --}}
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

    {{-- MODAL 2: KONFIRMASI VERIFIKASI PEMBAYARAN --}}
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

    {{-- MODAL 3: POPUP PREVIEW BUKTI TRANSFER TERBARU --}}
    <div x-show="showProofModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
         style="display: none;">
        
        <div @click.away="showProofModal = false" class="bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2.5rem] p-6 max-w-xl w-full shadow-2xl space-y-4 text-center my-auto">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <div class="flex items-center gap-2 text-[#3e2723]">
                    <i class="fa-solid fa-receipt text-lg text-emerald-600"></i>
                    <h3 class="font-black text-sm">Bukti Transfer - <span x-text="proofOrderNumber"></span></h3>
                </div>
                <button @click="showProofModal = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- FOTO BUKTI UTAMA --}}
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-left text-gray-500 mb-1">Foto Bukti Terbaru</p>
                <div class="relative bg-gray-100 rounded-2xl overflow-hidden border border-gray-200 max-h-[50vh] flex items-center justify-center">
                    <img :src="modalProofUrl" alt="Foto Bukti Transfer Terbaru" class="max-h-[48vh] w-auto object-contain rounded-xl shadow">
                </div>
            </div>

            {{-- 📜 TOMBOL DENGAN BADGE UNTUK MEMBUKA SUB-MODAL RIWAYAT --}}
            <div class="pt-2 flex flex-col gap-2">
                <template x-if="proofHistoryList.length > 0">
                    <button type="button" @click="openHistoryModal()" class="w-full py-2.5 px-4 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-400 text-amber-900 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa-solid fa-clock-rotate-left text-amber-700"></i>
                        <span>Cek Riwayat Upload Bukti</span>
                        <span class="bg-amber-600 text-white text-[10px] px-2 py-0.5 rounded-full font-black" x-text="proofHistoryList.length + ' Foto'"></span>
                    </button>
                </template>
            </div>

            <div class="pt-2 flex justify-between items-center gap-2 border-t border-gray-100">
                <a :href="modalProofUrl" target="_blank" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-up-right-from-square"></i> Tab Baru
                </a>
                <button @click="showProofModal = false" type="button" class="px-5 py-2 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow hover:bg-[#2c1b18] transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL 4: SUB-MODAL AUDIT TRAIL RIWAYAT UPLOAD FOTO BUKTI --}}
    <div x-show="showHistoryModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md"
         style="display: none;">
        
        <div @click.away="showHistoryModal = false" class="bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2.5rem] p-6 max-w-2xl w-full shadow-2xl space-y-4 my-auto max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <div class="flex items-center gap-2 text-[#3e2723]">
                    <i class="fa-solid fa-clock-rotate-left text-lg text-amber-700"></i>
                    <div>
                        <h3 class="font-black text-sm">Riwayat Unggahan Bukti Transfer</h3>
                        <p class="text-[10px] text-gray-500">Order: <span x-text="proofOrderNumber" class="font-bold text-[#3e2723]"></span></p>
                    </div>
                </div>
                <button @click="showHistoryModal = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- KETERANGAN & WARNING PERGANTIAN GAMBAR --}}
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-left text-xs text-amber-900 flex items-center gap-2">
                <i class="fa-solid fa-shield-cat text-amber-600 text-xl shrink-0"></i>
                <div>
                    <p class="font-bold">Audit History Pergantian Gambar</p>
                    <p class="text-[11px] text-amber-800">
                        Di bawah ini adalah daftar foto mana saja dan mana dulu yang pernah diunggah oleh pelanggan. Klik foto untuk melihat tampilan lebih jelas.
                    </p>
                </div>
            </div>

            {{-- DISPLAY PRATINJAU FOTO TERPILIH DARI RIWAYAT --}}
            <div class="bg-gray-100 rounded-2xl p-2 border border-gray-200 text-center">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Foto Dipilih</p>
                <div class="max-h-[35vh] flex items-center justify-center overflow-hidden">
                    <img :src="selectedHistoryImgUrl" class="max-h-[33vh] w-auto object-contain rounded-lg shadow" alt="Foto Riwayat Terpilih">
                </div>
            </div>

            {{-- DAFTAR LIST THUMBNAIL FOTO DARI AWAL HINGGA AKHIR --}}
            <div class="space-y-2 text-left">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-600">Daftar Foto Berdasarkan Urutan Upload:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="(item, index) in proofHistoryList" :key="index">
                        <div class="p-2 bg-gray-50 border rounded-xl space-y-1 text-center cursor-pointer hover:border-emerald-500 transition shadow-sm"
                             :class="selectedHistoryImgUrl === item.url ? 'border-2 border-emerald-600 bg-emerald-50/50' : 'border-gray-200'"
                             @click="selectedHistoryImgUrl = item.url">
                            
                            <div class="h-24 bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                                <img :src="item.url" class="h-full w-auto object-cover rounded" alt="Riwayat Foto">
                            </div>
                            
                            <div class="pt-1">
                                <p class="text-[11px] font-black text-[#3e2723]" x-text="'Upload #' + (item.sequence || (index + 1))"></p>
                                <p class="text-[9px] font-medium text-gray-500 mt-0.5" x-text="item.uploaded_at"></p>
                            </div>

                            <a :href="item.url" target="_blank" @click.stop class="block pt-1 text-[10px] font-bold text-blue-600 hover:underline">
                                <i class="fa-solid fa-magnifying-glass-plus"></i> Perbesar
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button @click="showHistoryModal = false" type="button" class="px-5 py-2 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow hover:bg-[#2c1b18] transition">
                    Kembali
                </button>
            </div>
        </div>
    </div>

</div>

{{-- MODAL POPUP: KONFIRMASI UBAH STATUS PRODUKSI (REFERENSI JADWAL & PRODUKSI PO) --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/60 backdrop-blur-3xl border border-white/70 shadow-2xl relative space-y-5 my-auto text-center">
        
        <!-- Icon Peringatan -->
        <div id="confirmModalIconBg" class="w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner">
            <i id="confirmModalIcon" class="fa-solid"></i>
        </div>

        <div>
            <h3 id="confirmModalTitle" class="text-lg font-black text-[#3e2723]">Konfirmasi Tindakan</h3>
            <p id="confirmModalDescription" class="text-xs font-medium text-gray-600 mt-1 leading-relaxed px-2">
                Apakah Anda yakin ingin memperbarui status pesanan ini?
            </p>
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="button" onclick="cancelStatusChange()" class="flex-1 py-2.5 text-xs font-bold rounded-xl bg-white/60 border border-white text-[#3e2723] hover:bg-white transition shadow-sm">
                Batal
            </button>
            <button type="button" id="confirmSubmitBtn" class="flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
    let activeSelectElement = null;
    let activeFormId = null;
    let originalValue = null;

    // Intercept dropdown status produksi
    function handleStatusChange(selectElement, orderId, orderNumber, currentStatus) {
        const selectedVal = selectElement.value;

        // Jika nilai yang dipilih sama dengan status saat ini, abaikan
        if (selectedVal === currentStatus) return;

        activeSelectElement = selectElement;
        activeFormId = `status-form-${orderId}`;
        originalValue = currentStatus;

        const modal = document.getElementById('confirmModal');
        const iconBg = document.getElementById('confirmModalIconBg');
        const icon = document.getElementById('confirmModalIcon');
        const title = document.getElementById('confirmModalTitle');
        const desc = document.getElementById('confirmModalDescription');
        const submitBtn = document.getElementById('confirmSubmitBtn');

        // Konfigurasi visual modal berdasarkan status yang dipilih
        if (selectedVal === 'pending') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-amber-500/10 text-amber-700 border border-amber-500/20';
            icon.className = 'fa-solid fa-hourglass-start';
            title.innerText = 'Ubah ke Pending?';
            desc.innerHTML = `Status pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan dikembalikan menjadi <span class="text-amber-700 font-bold">PENDING</span>.`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-amber-600 hover:bg-amber-700';

        } else if (selectedVal === 'confirmed') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-indigo-500/10 text-indigo-700 border border-indigo-500/20';
            icon.className = 'fa-solid fa-thumbs-up';
            title.innerText = 'Konfirmasi Pesanan?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-indigo-700 font-bold">CONFIRMED</span> (Siap diproses dapur).`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-indigo-600 hover:bg-indigo-700';

        } else if (selectedVal === 'preparing') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-blue-500/10 text-blue-700 border border-blue-500/20';
            icon.className = 'fa-solid fa-fire-burner';
            title.innerText = 'Mulai Produksi Dapur?';
            desc.innerHTML = `Status pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan diubah menjadi <span class="text-blue-700 font-bold">PREPARING</span> (Sedang diproduksi/dipanggang).`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-blue-600 hover:bg-blue-700';

        } else if (selectedVal === 'ready') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-purple-500/10 text-purple-700 border border-purple-500/20';
            icon.className = 'fa-solid fa-box-open';
            title.innerText = 'Pesanan Siap (Ready)?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-purple-700 font-bold">READY</span> (Siap diambil/dikirim).`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-purple-600 hover:bg-purple-700';

        } else if (selectedVal === 'completed') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-emerald-500/10 text-emerald-700 border border-emerald-500/20';
            icon.className = 'fa-solid fa-circle-check';
            title.innerText = 'Pesanan Selesai?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-emerald-700 font-bold">SELESAI</span> dan dipindahkan ke halaman <strong>History Pesanan</strong>.`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-emerald-600 hover:bg-emerald-700';

        } else if (selectedVal === 'cancelled') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-rose-500/10 text-rose-700 border border-rose-500/20';
            icon.className = 'fa-solid fa-triangle-exclamation';
            title.innerText = 'Batalkan Pesanan?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-rose-700 font-bold">BATAL</span> dan dipindahkan ke halaman <strong>History Pesanan</strong>.`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-rose-600 hover:bg-rose-700';
        }

        submitBtn.onclick = function() {
            document.getElementById(activeFormId).submit();
        };

        modal.classList.remove('hidden');
    }

    function cancelStatusChange() {
        if (activeSelectElement && originalValue) {
            activeSelectElement.value = originalValue;
        }
        document.getElementById('confirmModal').classList.add('hidden');
        activeSelectElement = null;
        activeFormId = null;
        originalValue = null;
    }
</script>
@endsection