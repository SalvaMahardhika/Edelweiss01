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

    init() {
        window.alpineScope = this;
    },

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
        
        if (typeof historyList === 'string') {
            try {
                this.proofHistoryList = JSON.parse(historyList);
            } catch (e) {
                this.proofHistoryList = [];
            }
        } else {
            this.proofHistoryList = historyList || [];
        }
        
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
    @php
        $rawOrders = $orders->getCollection();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Menunggu Verifikasi --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-900/60 uppercase tracking-wider">Perlu Dikonfirmasi</p>
                <h3 class="text-2xl font-black text-amber-950 mt-1">
                    {{ $rawOrders->filter(function($o) {
                        $st = strtolower(is_object($o->payment_status) ? ($o->payment_status->value ?? $o->payment_status->name ?? '') : (string)$o->payment_status);
                        return in_array($st, ['unpaid', 'pending', '']);
                    })->count() }} Transaksi
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
                    {{ $rawOrders->filter(function($o) {
                        $st = strtolower(is_object($o->payment_status) ? ($o->payment_status->value ?? $o->payment_status->name ?? '') : (string)$o->payment_status);
                        return in_array($st, ['paid', 'lunas', 'dp', 'partial']);
                    })->count() }} Transaksi
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
                    {{ $orders->total() }} Transaksi
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-xl shadow-sm">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
        </div>
    </div>

    {{-- 2. BAR PENCARIAN & LIVE AUTO-FILTER (DATATABLES AJAX) --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1 flex-wrap">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="filter_search" value="{{ request('search') }}" placeholder="Cari No. Order, Nama, atau No. WhatsApp..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
            </div>

            <select id="filter_status_produksi" class="px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                <option value="ALL">Semua Status Pesanan</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="preparing">Preparing (Dapur)</option>
                <option value="ready">Ready (Siap)</option>
                <option value="completed">Completed (Selesai)</option>
                <option value="cancelled">Cancelled (Batal)</option>
            </select>

            <select id="filter_status_bayar" class="px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                <option value="ALL">Semua Status Bayar</option>
                <option value="unpaid">Belum Lunas (Unpaid)</option>
                <option value="partial">Uang Muka (DP 50%)</option>
                <option value="paid">Lunas (Paid)</option>
            </select>

            <select id="filter_has_proof" class="px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                <option value="">Semua Bukti TF</option>
                <option value="1">Ada Bukti TF</option>
                <option value="0">Belum Ada Bukti</option>
            </select>

            <button type="button" id="resetManualFilterBtn" class="px-4 py-2.5 bg-white/60 text-[#3e2723] text-xs font-bold rounded-xl border border-white/50 hover:bg-white transition flex items-center justify-center gap-1 shadow-sm">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
        </div>
    </div>

    {{-- 3. TABEL DATA ORDER MANUAL (DATATABLES SERVER-SIDE AJAX) --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden space-y-4">
        <h3 class="text-base font-bold text-[#3e2723] flex items-center gap-2">
            <i class="fa-brands fa-whatsapp text-emerald-600 text-lg"></i> Daftar Pesanan Transfer Manual (WhatsApp)
        </h3>

        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 p-2 shadow-inner">
            <table id="manualOrdersTable" class="w-full text-left border-collapse">
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
                    {{-- Diisi secara dinamis oleh AJAX DataTables --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL 1: ALAMAT LENGKAP PENGIRIMAN --}}
    <div x-show="showAddressModal" 
         x-transition
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
         x-transition
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

            <form :action="verifyFormAction" method="POST" class="pt-2 flex gap-3 justify-center" @submit.prevent="submitVerifyPayment($el)">
                @csrf
                @method('PATCH')
                <button type="button" @click="showVerifyModal = false" class="px-5 py-2.5 bg-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-300 transition w-1/2">
                    Batal
                </button>
                <button type="submit" id="btnConfirmVerify" class="px-5 py-2.5 bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition w-1/2 flex items-center justify-center gap-1">
                    <span>Ya, Verifikasi</span>
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL 3: POPUP PREVIEW BUKTI TRANSFER TERBARU --}}
    <div x-show="showProofModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
         style="display: none;">
        
        <div @click.away="showProofModal = false" class="bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2.5rem] p-6 max-w-xl w-full shadow-2xl space-y-4 text-center my-auto">
            
            <div class="flex items-center justify-between pb-3 border-gray-200">
                <div class="flex items-center gap-2 text-[#3e2723]">
                    <i class="fa-solid fa-receipt text-lg text-emerald-600"></i>
                    <h3 class="font-black text-sm">Bukti Transfer - <span x-text="proofOrderNumber"></span></h3>
                </div>
                <button @click="showProofModal = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-left text-gray-500 mb-1">Foto Bukti Terbaru</p>
                <div class="relative bg-gray-100 rounded-2xl overflow-hidden border border-gray-200 max-h-[50vh] flex items-center justify-center">
                    <img :src="modalProofUrl" alt="Foto Bukti Transfer Terbaru" class="max-h-[48vh] w-auto object-contain rounded-xl shadow">
                </div>
            </div>

            <div class="pt-2 flex flex-col gap-2">
                <template x-if="proofHistoryList && proofHistoryList.length > 0">
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
         x-transition
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

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-left text-xs text-amber-900 flex items-center gap-2">
                <i class="fa-solid fa-shield-cat text-amber-600 text-xl shrink-0"></i>
                <div>
                    <p class="font-bold">Audit History Pergantian Gambar</p>
                    <p class="text-[11px] text-amber-800">
                        Di bawah ini adalah daftar foto mana saja dan mana dulu yang pernah diunggah oleh pelanggan. Klik foto untuk melihat tampilan lebih jelas.
                    </p>
                </div>
            </div>

            <div class="bg-gray-100 rounded-2xl p-2 border border-gray-200 text-center">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Foto Dipilih</p>
                <div class="max-h-[35vh] flex items-center justify-center overflow-hidden">
                    <img :src="selectedHistoryImgUrl" class="max-h-[33vh] w-auto object-contain rounded-lg shadow" alt="Foto Riwayat Terpilih">
                </div>
            </div>

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

{{-- MODAL POPUP: KONFIRMASI UBAH STATUS PRODUKSI --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/60 backdrop-blur-3xl border border-white/70 shadow-2xl relative space-y-5 my-auto text-center">
        
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
            <button type="button" id="confirmSubmitBtn" class="flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition flex items-center justify-center gap-1">
                <span>Ya, Lanjutkan</span>
            </button>
        </div>
    </div>
</div>

<script>
    let activeSelectElement = null;
    let activeFormId = null;
    let originalValue = null;
    let targetStatusVal = null;

    function triggerAlpineAddress(name, number, address) {
        if (window.alpineScope) {
            window.alpineScope.openAddressModal(name, number, address);
        }
    }

    function triggerAlpineVerify(actionUrl, number) {
        if (window.alpineScope) {
            window.alpineScope.openVerifyModal(actionUrl, number);
        }
    }

    function triggerAlpineProof(url, number, historyList) {
        if (window.alpineScope) {
            window.alpineScope.openProofModal(url, number, historyList);
        }
    }

    function initManualOrdersDataTable() {
        if (typeof $ === 'undefined' || !$.fn.DataTable) {
            return;
        }

        $.fn.dataTable.ext.errMode = 'throw';

        let table = $('#manualOrdersTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: "{{ route('admin.orders.manual') }}",
                type: "GET",
                data: function (d) {
                    d.search = $('#filter_search').val();
                    d.status = $('#filter_status_produksi').val();
                    d.status_bayar = $('#filter_status_bayar').val();
                    d.has_proof = $('#filter_has_proof').val();
                }
            },
            columns: [
                {
                    data: 'order_number',
                    name: 'order_number',
                    render: function (data, type, row) {
                        let cleanPhone = row.customer_phone ? String(row.customer_phone).replace(/[^0-9]/g, '') : '';
                        if (cleanPhone.startsWith('0')) {
                            cleanPhone = '62' + cleanPhone.substring(1);
                        }

                        let createdAtFormatted = row.created_at || '-';
                        let custName = row.customer_name ? escapeHtml(row.customer_name) : 'Pelanggan';

                        return `
                            <div>
                                <p class="font-black text-[#3e2723]">${row.order_number || '-'}</p>
                                <p class="font-bold text-[#3e2723]/90 mt-0.5">${custName}</p>
                                <a href="https://wa.me/${cleanPhone}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline mt-0.5">
                                    <i class="fa-brands fa-whatsapp"></i> ${row.customer_phone || '-'}
                                </a>
                                <p class="text-[10px] font-semibold text-gray-500 mt-1 flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-amber-700"></i> Dibuat: ${createdAtFormatted}
                                </p>
                            </div>
                        `;
                    }
                },
                {
                    data: 'fulfill_at',
                    name: 'fulfill_at',
                    render: function (data, type, row) {
                        let orderTypeVal = row.order_type && typeof row.order_type === 'object' ? (row.order_type.value || row.order_type.name) : row.order_type;
                        let isDelivery = String(orderTypeVal).toLowerCase() === 'delivery';

                        let badge = isDelivery
                            ? `<button type="button" onclick="triggerAlpineAddress('${escapeHtml(row.customer_name || '')}', '${row.order_number}', '${escapeHtml(row.delivery_address || 'Alamat tidak dicantumkan')}')" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800 hover:bg-blue-200 transition flex items-center gap-1 shadow-sm"><i class="fa-solid fa-truck text-[9px]"></i> DELIVERY (Cek Alamat)</button>`
                            : `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800 inline-block"><i class="fa-solid fa-store text-[9px]"></i> PICKUP</span>`;

                        let fulfillTime = row.fulfill_at ? row.fulfill_at : '-';

                        return `
                            <div>
                                ${badge}
                                <p class="font-bold text-[#3e2723] mt-1.5">${fulfillTime}</p>
                            </div>
                        `;
                    }
                },
                {
                    data: 'id',
                    name: 'items',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let itemList = '<ul class="space-y-0.5">';
                        if (row.items && row.items.length > 0) {
                            row.items.forEach(function(item) {
                                itemList += `<li class="text-[11px] text-[#3e2723]/90"><strong class="text-[#3e2723]">${item.quantity}x</strong> ${escapeHtml(item.product_name || 'Item')}</li>`;
                            });
                        } else {
                            itemList += '<li class="text-gray-400 italic">Tidak ada item</li>';
                        }
                        itemList += '</ul>';

                        if (row.notes) {
                            itemList += `<p class="text-[10px] italic text-amber-900 mt-1 bg-amber-50/60 p-1 rounded border border-amber-200/50">Note: "${escapeHtml(row.notes)}"</p>`;
                        }
                        return itemList;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        let statusVal = row.status && typeof row.status === 'object' ? (row.status.value || row.status.name) : row.status;
                        statusVal = String(statusVal || 'pending').toLowerCase();
                        
                        let bgClass = 'bg-amber-500';
                        if (statusVal === 'completed') bgClass = 'bg-emerald-600';
                        else if (statusVal === 'preparing') bgClass = 'bg-blue-600';
                        else if (statusVal === 'ready') bgClass = 'bg-purple-600';
                        else if (statusVal === 'confirmed') bgClass = 'bg-indigo-600';
                        else if (statusVal === 'cancelled') bgClass = 'bg-rose-600';

                        let updateUrl = "{{ route('admin.po.updateStatus', ':id') }}".replace(':id', row.id);
                        let csrf = '{{ csrf_field() }}';

                        return `
                            <form id="status-form-${row.id}" method="POST" action="${updateUrl}">
                                ${csrf}
                                <input type="hidden" name="_method" value="PATCH">
                                <select name="status" 
                                        onchange="handleStatusChange(this, '${row.id}', '${row.order_number}', '${statusVal}')" 
                                        class="w-full text-xs font-bold px-2 py-1.5 rounded-xl border border-white/50 shadow-md cursor-pointer transition focus:outline-none text-white ${bgClass}">
                                    <option value="pending" class="bg-white text-gray-800" ${statusVal === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="confirmed" class="bg-white text-gray-800" ${statusVal === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                    <option value="preparing" class="bg-white text-gray-800" ${statusVal === 'preparing' ? 'selected' : ''}>Preparing</option>
                                    <option value="ready" class="bg-white text-gray-800" ${statusVal === 'ready' ? 'selected' : ''}>Ready</option>
                                    <option value="completed" class="bg-white text-gray-800" ${statusVal === 'completed' ? 'selected' : ''}>Completed (Selesai)</option>
                                    <option value="cancelled" class="bg-white text-gray-800" ${statusVal === 'cancelled' ? 'selected' : ''}>Batal</option>
                                </select>
                            </form>
                        `;
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    className: 'text-center',
                    render: function (data, type, row) {
                        let payPlan = row.payment_plan && typeof row.payment_plan === 'object' ? (row.payment_plan.value || row.payment_plan.name) : row.payment_plan;
                        let isDp = String(payPlan).toLowerCase() === 'dp';

                        let planBadge = isDp
                            ? `<span class="text-[10px] text-amber-800 font-bold bg-amber-100 px-2 py-0.5 rounded-full inline-block mt-0.5">DP 50%</span>`
                            : `<span class="text-[10px] text-emerald-800 font-bold bg-emerald-100 px-2 py-0.5 rounded-full inline-block mt-0.5">Full Payment</span>`;

                        return `
                            <div>
                                <p class="font-black text-[#3e2723]">${row.total_amount || 'Rp 0'}</p>
                                ${planBadge}
                            </div>
                        `;
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    className: 'text-center space-y-1.5',
                    render: function (data, type, row) {
                        let payStatus = row.payment_status && typeof row.payment_status === 'object' ? (row.payment_status.value || row.payment_status.name) : row.payment_status;
                        payStatus = String(payStatus || 'unpaid').toLowerCase();

                        let isPaid = ['paid', 'lunas'].includes(payStatus);
                        let isPartial = ['dp', 'partial'].includes(payStatus);

                        let statusBadge = isPaid
                            ? `<span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-emerald-600 text-white shadow-sm inline-block">LUNAS</span>`
                            : (isPartial 
                                ? `<span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-amber-500 text-white shadow-sm inline-block">DP DITERIMA</span>`
                                : `<span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-600 text-white shadow-sm inline-block">BELUM BAYAR</span>`);

                        let proofBtn = '';
                        let proofHistory = row.payment_proof_history || [];
                        let proofUrl = row.payment_proof ? '/img/buktitf/' + row.payment_proof : (proofHistory.length > 0 ? proofHistory[proofHistory.length - 1].url : '');

                        if (proofUrl) {
                            let jsonHistory = escapeHtml(JSON.stringify(proofHistory));
                            let badgeHistory = proofHistory.length > 1 ? `<span class="bg-amber-600 text-white text-[9px] px-1.5 py-0.2 rounded-full font-extrabold ml-0.5" title="Customer ganti gambar ${proofHistory.length}x">${proofHistory.length}x Upload</span>` : '';

                            proofBtn = `<button type="button" onclick="triggerAlpineProof('${proofUrl}', '${row.order_number}', '${jsonHistory}')" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100/80 hover:bg-emerald-200 border border-emerald-300 px-2 py-1 rounded-lg shadow-sm transition"><i class="fa-solid fa-image text-emerald-700"></i> Cek Bukti TF ${badgeHistory}</button>`;
                        } else {
                            proofBtn = `<span class="text-[10px] text-gray-400 italic block">Belum ada bukti</span>`;
                        }

                        return `
                            <div>${statusBadge}</div>
                            <div>${proofBtn}</div>
                        `;
                    }
                },
                {
                    data: 'id',
                    name: 'action',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let cleanPhone = row.customer_phone ? String(row.customer_phone).replace(/[^0-9]/g, '') : '';
                        if (cleanPhone.startsWith('0')) {
                            cleanPhone = '62' + cleanPhone.substring(1);
                        }

                        let verifyUrl = "{{ route('admin.orders.verifyPayment', ':id') }}".replace(':id', row.id);
                        let waMsg = encodeURIComponent(`Halo Kak ${row.customer_name}, kami dari Admin Edelweiss Bakery ingin mengonfirmasi pesanan No. *${row.order_number}*. Apakah bukti transfer sudah dikirimkan?`);

                        return `
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick="triggerAlpineVerify('${verifyUrl}', '${row.order_number}')" class="p-2 bg-emerald-700 text-white rounded-xl shadow hover:bg-emerald-800 transition text-xs font-bold flex items-center gap-1" title="Tandai Pembayaran Lunas/DP">
                                    <i class="fa-solid fa-check"></i> Verifikasi
                                </button>
                                <a href="https://wa.me/${cleanPhone}?text=${waMsg}" target="_blank" class="p-2 bg-emerald-500 text-white rounded-xl shadow hover:bg-emerald-600 transition text-xs font-bold" title="Chat WA Pelanggan">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                search: "Cari Pesanan:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pesanan",
                paginate: {
                    previous: "<i class='fa-solid fa-chevron-left'></i>",
                    next: "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        let searchTimer;
        $('#filter_search').off('keyup').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                table.draw();
            }, 400);
        });

        $('#filter_status_produksi, #filter_status_bayar, #filter_has_proof').off('change').on('change', function() {
            table.draw();
        });

        $('#resetManualFilterBtn').off('click').on('click', function() {
            $('#filter_search').val('');
            $('#filter_status_produksi').val('ALL');
            $('#filter_status_bayar').val('ALL');
            $('#filter_has_proof').val('');
            table.draw();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initManualOrdersDataTable);
    } else {
        initManualOrdersDataTable();
    }

    // 🔴 LISTEN EVENT REALTIME DARI LARAVEL REVERB VIA LARAVEL ECHO
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.Echo !== 'undefined') {
            window.Echo.private('admin.orders')
                .listen('OrderUpdated', (e) => {
                    console.log('Perubahan order terdeteksi via Reverb. Memperbarui tabel...');
                    if ($.fn.DataTable.isDataTable('#manualOrdersTable')) {
                        $('#manualOrdersTable').DataTable().ajax.reload(null, false);
                    }
                });
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function handleStatusChange(selectElement, orderId, orderNumber, currentStatus) {
        const selectedVal = selectElement.value;

        if (selectedVal === currentStatus) return;

        activeSelectElement = selectElement;
        activeFormId = `status-form-${orderId}`;
        originalValue = currentStatus;
        targetStatusVal = selectedVal;

        const modal = document.getElementById('confirmModal');
        const iconBg = document.getElementById('confirmModalIconBg');
        const icon = document.getElementById('confirmModalIcon');
        const title = document.getElementById('confirmModalTitle');
        const desc = document.getElementById('confirmModalDescription');
        const submitBtn = document.getElementById('confirmSubmitBtn');

        if (selectedVal === 'pending') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-amber-500/10 text-amber-700 border border-amber-500/20';
            icon.className = 'fa-solid fa-hourglass-start';
            title.innerText = 'Ubah ke Pending?';
            desc.innerHTML = `Status pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan dikembalikan menjadi <span class="text-amber-700 font-bold">PENDING</span>.`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-amber-600 hover:bg-amber-700 flex items-center justify-center gap-1';

        } else if (selectedVal === 'confirmed') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-indigo-500/10 text-indigo-700 border border-indigo-500/20';
            icon.className = 'fa-solid fa-thumbs-up';
            title.innerText = 'Konfirmasi Pesanan?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-indigo-700 font-bold">CONFIRMED</span> (Siap diproses dapur).`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-indigo-600 hover:bg-indigo-700 flex items-center justify-center gap-1';

        } else if (selectedVal === 'preparing') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-blue-500/10 text-blue-700 border border-blue-500/20';
            icon.className = 'fa-solid fa-fire-burner';
            title.innerText = 'Mulai Produksi Dapur?';
            desc.innerHTML = `Status pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan diubah menjadi <span class="text-blue-700 font-bold">PREPARING</span> (Sedang diproduksi/dipanggang).`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-blue-600 hover:bg-blue-700 flex items-center justify-center gap-1';

        } else if (selectedVal === 'ready') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-purple-500/10 text-purple-700 border border-purple-500/20';
            icon.className = 'fa-solid fa-box-open';
            title.innerText = 'Pesanan Siap (Ready)?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-purple-700 font-bold">READY</span> (Siap diambil/dikirim).`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-purple-600 hover:bg-purple-700 flex items-center justify-center gap-1';

        } else if (selectedVal === 'completed') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-emerald-500/10 text-emerald-700 border border-emerald-500/20';
            icon.className = 'fa-solid fa-circle-check';
            title.innerText = 'Pesanan Selesai?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-emerald-700 font-bold">SELESAI</span> dan dipindahkan ke halaman <strong>History Pesanan</strong>.`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-emerald-600 hover:bg-emerald-700 flex items-center justify-center gap-1';

        } else if (selectedVal === 'cancelled') {
            iconBg.className = 'w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto shadow-inner bg-rose-500/10 text-rose-700 border border-rose-500/20';
            icon.className = 'fa-solid fa-triangle-exclamation';
            title.innerText = 'Batalkan Pesanan?';
            desc.innerHTML = `Pesanan <strong class="text-[#3e2723]">${orderNumber}</strong> akan ditandai <span class="text-rose-700 font-bold">BATAL</span> dan dipindahkan ke halaman <strong>History Pesanan</strong>.`;
            submitBtn.className = 'flex-1 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition bg-rose-600 hover:bg-rose-700 flex items-center justify-center gap-1';
        }

        submitBtn.onclick = function() {
            submitStatusChangeAjax();
        };

        modal.classList.remove('hidden');
    }

    // ⚡ PROSES UBAH STATUS VIA AJAX TANPA RELOAD PAGE
    function submitStatusChangeAjax() {
        if (!activeFormId) return;

        const form = document.getElementById(activeFormId);
        if (!form) return;

        const actionUrl = form.getAttribute('action');
        const submitBtn = document.getElementById('confirmSubmitBtn');
        const origBtnText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Memproses...`;

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PATCH',
                status: targetStatusVal
            },
            success: function(res) {
                document.getElementById('confirmModal').classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = origBtnText;

                // Reload DataTables secara halus (tanpa reset pagination)
                if ($.fn.DataTable.isDataTable('#manualOrdersTable')) {
                    $('#manualOrdersTable').DataTable().ajax.reload(null, false);
                }

                activeSelectElement = null;
                activeFormId = null;
                originalValue = null;
                targetStatusVal = null;
            },
            error: function(err) {
                alert('Gagal memperbarui status pesanan. Silakan coba lagi.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = origBtnText;
                cancelStatusChange();
            }
        });
    }

    // ⚡ PROSES VERIFIKASI PEMBAYARAN VIA AJAX TANPA RELOAD PAGE
    function submitVerifyPayment(formEl) {
        const btn = document.getElementById('btnConfirmVerify');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Memproses...`;

        $.ajax({
            url: formEl.action,
            type: 'POST',
            data: $(formEl).serialize(),
            success: function(res) {
                if (window.alpineScope) {
                    window.alpineScope.showVerifyModal = false;
                }
                btn.disabled = false;
                btn.innerHTML = origText;

                if ($.fn.DataTable.isDataTable('#manualOrdersTable')) {
                    $('#manualOrdersTable').DataTable().ajax.reload(null, false);
                }
            },
            error: function(err) {
                alert('Gagal memverifikasi pembayaran.');
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        });
    }

    function cancelStatusChange() {
        if (activeSelectElement && originalValue) {
            activeSelectElement.value = originalValue;
        }
        document.getElementById('confirmModal').classList.add('hidden');
        activeSelectElement = null;
        activeFormId = null;
        originalValue = null;
        targetStatusVal = null;
    }
</script>
@endsection