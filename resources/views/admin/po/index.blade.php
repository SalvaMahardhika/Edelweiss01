@extends('admin_layouts.master')

@section('page_title', 'Jadwal & Produksi Pre-Order (PO)')

@section('content')
<style>
    .dataTables_scrollBody {
        min-height: 380px !important;
        max-height: calc(100vh - 22rem) !important;
    }
</style>

<div x-data="{ 
    // State Modal Alamat Pengiriman
    showAddressModal: false, 
    modalCustomerName: '', 
    modalOrderNumber: '', 
    modalAddress: '',

    init() {
        window.alpineScope = this;
    },

    openAddressModal(name, number, address) {
        this.modalCustomerName = name;
        this.modalOrderNumber = number;
        this.modalAddress = address;
        this.showAddressModal = true;
    }
}" class="min-h-full flex flex-col space-y-6 pb-8 overflow-y-auto pr-2">

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

    {{-- 1. RINGKASAN STATISTIK PO (REALTIME AUTO-SYNC) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-shrink-0">
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Jadwal PO Hari Ini</p>
                <h3 id="stat_today_po" class="text-2xl font-black text-[#3e2723] mt-1">{{ $todayPO ?? 0 }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-clock font-bold"></i>
            </div>
        </div>

        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-900/60 uppercase tracking-wider">Menunggu Konfirmasi</p>
                <h3 id="stat_pending_po" class="text-2xl font-black text-amber-950 mt-1">{{ $pendingPO ?? 0 }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-blue-800/60 uppercase tracking-wider">Sedang Diproduksi Dapur</p>
                <h3 id="stat_preparing_po" class="text-2xl font-black text-blue-900 mt-1">{{ $preparingPO ?? 0 }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-fire-burner"></i>
            </div>
        </div>
    </div>

    {{-- 2. BAR PENCARIAN & LIVE AUTO-FILTER --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 flex-shrink-0 space-y-4">
        {{-- INPUT PENCARIAN REALTIME --}}
        <div class="relative w-full">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" id="filter_search" value="{{ request('search') }}" placeholder="Cari No. Order, Nama Pelanggan, atau No. WhatsApp..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
        </div>

        {{-- FILTER DROPDOWN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Kirim / Ambil</label>
                <input type="date" id="filter_date" value="{{ request('date') }}" class="w-full mt-1 px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Produksi</label>
                <select id="filter_status" class="w-full mt-1 px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Status Aktif</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="preparing">Preparing (Dipanggang)</option>
                    <option value="ready">Ready (Siap)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Pembayaran</label>
                <select id="filter_payment_status" class="w-full mt-1 px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Pembayaran</option>
                    <option value="unpaid">Belum Bayar</option>
                    <option value="partial">DP (Ada Sisa Pelunasan)</option>
                    <option value="paid">Lunas</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="button" id="resetPoFilterBtn" class="w-full py-2.5 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center shadow-sm flex items-center justify-center gap-1">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- 3. TABEL UTAMA MANAJEMEN PO (DATATABLES AJAX REALTIME) --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 flex-1 flex flex-col">
        {{-- Header Bar Atas Tabel: Tombol Reset Urutan Tabel --}}
        <div class="flex justify-between items-center mb-3 px-1 flex-wrap gap-2">
            <h3 class="text-base font-bold text-[#3e2723] flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-[#3e2723] text-lg"></i> Antrean Produksi & Jadwal Pre-Order
            </h3>
            <button type="button" id="btnResetPoSort" onclick="resetPoTableOrder()" class="px-3 py-1.5 bg-white/80 border border-white/60 hover:bg-[#3e2723] hover:text-white text-[#3e2723] text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-down-short-wide"></i> Reset Urutan Tabel
            </button>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 p-2 shadow-inner flex-1 flex flex-col">
            <table id="poTable" class="w-full min-w-[800px] text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-4 py-3.5">No. Order & Pelanggan</th>
                        <th class="px-4 py-3.5">Item Pesanan (Kue)</th>
                        <th class="px-4 py-3.5 text-center">Jadwal Siap (`fulfill_at`)</th>
                        <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
                        <th class="px-4 py-3.5 text-center w-40">Status Produksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-xs font-medium">
                    {{-- Data dimuat dinamis via AJAX DataTables --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL POPUP: ALAMAT PENGIRIMAN --}}
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
                    <div class="p-3 bg-blue-50/70 border border-blue-200/60 rounded-xl font-semibold text-gray-800 leading-relaxed mt-1 whitespace-pre-line" x-text="modalAddress"></div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button @click="showAddressModal = false" type="button" class="px-5 py-2.5 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow hover:bg-[#2c1b18] transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

{{-- MODAL POPUP: KONFIRMASI UBAH STATUS PRODUKSI --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/80 backdrop-blur-3xl border border-white/70 shadow-2xl relative space-y-5 my-auto text-center">
        
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
    let poTable = null;
    let autoRefreshTimer = null;
    let searchTimer = null;

    function triggerAlpineAddress(name, number, address) {
        if (window.alpineScope) {
            window.alpineScope.openAddressModal(name, number, address);
        }
    }

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

                if ($.fn.DataTable.isDataTable('#poTable')) {
                    $('#poTable').DataTable().ajax.reload(null, false);
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

    function initPoDataTable() {
        if (typeof $ === 'undefined' || !$.fn.DataTable) return;

        $.fn.dataTable.ext.errMode = 'throw';

        poTable = $('#poTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            autoWidth: false,
            dom: 'rtip', // Menghilangkan searchbox bawaan DataTables
            scrollY: 'calc(100vh - 22rem)',
            scrollCollapse: true,
            order: [[2, 'asc']], // Default urutkan berdasarkan Jadwal Siap (fulfill_at)
            ajax: {
                url: "{{ route('admin.po.index') }}",
                type: "GET",
                global: false, // 🛑 PENTING: Mencegah trigger spinner/loading overlay global
                data: function (d) {
                    // MENGGUNAKAN FORMAT PERSIS SEPERTI MANUAL ORDER
                    d.search = $('#filter_search').val();
                    d.date = $('#filter_date').val();
                    d.status = $('#filter_status').val();
                    d.payment_status = $('#filter_payment_status').val();
                }
            },
            columns: [
                {
                    data: 'order_number',
                    name: 'order_number',
                    className: 'whitespace-nowrap',
                    orderable: true,
                    render: function (data, type, row) {
                        if (!row) return '-';
                        let orderTypeVal = row.order_type && typeof row.order_type === 'object' ? (row.order_type.value || row.order_type.name) : row.order_type;
                        let isPickup = String(orderTypeVal).toLowerCase() === 'pickup';
                        
                        let typeBadge = isPickup 
                            ? `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800 inline-block"><i class="fa-solid fa-store text-[9px]"></i> PICKUP</span>`
                            : `<button type="button" onclick="triggerAlpineAddress('${escapeHtml(row.customer_name || '')}', '${row.order_number}', '${escapeHtml(row.delivery_address || 'Alamat tidak dicantumkan')}')" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800 hover:bg-blue-200 transition flex items-center gap-1 shadow-sm"><i class="fa-solid fa-truck text-[9px]"></i> DELIVERY (Cek Alamat)</button>`;

                        let safeCustName = escapeHtml(row.customer_name || 'Pelanggan');
                        let cleanPhone = row.customer_phone ? String(row.customer_phone).replace(/[^0-9]/g, '') : '';
                        if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);

                        return `
                            <div>
                                <p class="font-black text-[#3e2723]">${row.order_number || '-'}</p>
                                <p class="font-bold text-[#3e2723]/90 mt-0.5">${safeCustName}</p>
                                <a href="https://wa.me/${cleanPhone}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline mt-0.5">
                                    <i class="fa-brands fa-whatsapp"></i> ${row.customer_phone || '-'}
                                </a>
                                <div class="mt-1.5">${typeBadge}</div>
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
                        if (!row) return '-';
                        let itemList = '<ul class="space-y-0.5">';
                        if (row.items && Array.isArray(row.items) && row.items.length > 0) {
                            row.items.forEach(function(item) {
                                itemList += `<li class="text-[11px] text-[#3e2723]/90"><strong class="text-[#3e2723]">${item.quantity || 1}x</strong> ${escapeHtml(item.product_name || '-')}</li>`;
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
                    data: 'fulfill_at',
                    name: 'fulfill_at',
                    className: 'text-center whitespace-nowrap',
                    orderable: true,
                    render: function (data) {
                        return data ? `<p class="font-bold text-xs text-[#3e2723]">${data}</p>` : `<span class="text-xs text-gray-400 italic">Belum Diset</span>`;
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    className: 'text-center space-y-1.5 whitespace-nowrap',
                    orderable: true,
                    render: function (data, type, row) {
                        if (!row) return '-';
                        let payPlan = row.payment_plan && typeof row.payment_plan === 'object' ? (row.payment_plan.value || row.payment_plan.name) : row.payment_plan;
                        let isDp = String(payPlan).toLowerCase() === 'dp';
                        let payStatus = row.payment_status && typeof row.payment_status === 'object' ? (row.payment_status.value || row.payment_status.name) : row.payment_status;
                        payStatus = String(payStatus || 'unpaid').toLowerCase();

                        let schemeBadge = isDp 
                            ? `<span class="text-[10px] text-purple-800 font-bold bg-purple-100 px-2 py-0.5 rounded-full inline-block">DP 50%</span>`
                            : `<span class="text-[10px] text-gray-700 font-bold bg-gray-100 px-2 py-0.5 rounded-full inline-block">FULL PAYMENT</span>`;

                        let statusBadge = '';
                        let amountText = '';

                        if (['paid', 'lunas'].includes(payStatus)) {
                            statusBadge = `<span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-emerald-600 text-white shadow-sm inline-block">LUNAS</span>`;
                            amountText = `<p class="text-[11px] font-bold text-emerald-700">${row.total_amount || 'Rp 0'}</p>`;
                        } else if (['partial', 'dp'].includes(payStatus)) {
                            statusBadge = `<span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-amber-500 text-white shadow-sm inline-block">DP DITERIMA</span>`;
                            let paidVal = row.amount_paid ? 'Rp ' + new Intl.NumberFormat('id-ID').format(row.amount_paid) : 'Rp 0';
                            amountText = `<p class="text-[11px] font-bold text-amber-700">DP: ${paidVal}</p>`;
                        } else {
                            statusBadge = `<span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-600 text-white shadow-sm inline-block">BELUM BAYAR</span>`;
                            amountText = `<p class="text-[11px] font-bold text-red-700">${row.total_amount || 'Rp 0'}</p>`;
                        }

                        return `
                            <div>${schemeBadge}</div>
                            <div class="mt-1">${statusBadge}</div>
                            <div class="mt-1">${amountText}</div>
                        `;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center whitespace-nowrap',
                    orderable: false,
                    render: function (data, type, row) {
                        if (!row) return '-';
                        let statusVal = row.status && typeof row.status === 'object' ? (row.status.value || row.status.name) : row.status;
                        statusVal = String(statusVal || 'pending').toLowerCase();

                        let payStatus = row.payment_status && typeof row.payment_status === 'object' ? (row.payment_status.value || row.payment_status.name) : row.payment_status;
                        payStatus = String(payStatus || 'unpaid').toLowerCase();

                        // 🔒 KUNCI JIKA STATUS PEMBAYARAN MASIH UNPAID
                        let isUnpaid = ['unpaid', 'pending', ''].includes(payStatus);

                        if (isUnpaid) {
                            return `
                                <div title="Pembayaran belum terverifikasi oleh webhook/sistem">
                                    <span class="w-full inline-flex items-center justify-center gap-1 text-xs font-bold px-2 py-1.5 rounded-xl border border-gray-300 bg-gray-200 text-gray-500 cursor-not-allowed shadow-inner">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Locked (Belum Bayar)
                                    </span>
                                </div>
                            `;
                        }

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
                }
            ],
            language: {
                search: "",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pesanan",
                paginate: {
                    previous: "<i class='fa-solid fa-chevron-left'></i>",
                    next: "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        $('#poTable').off('xhr.dt').on('xhr.dt', function (e, settings, json, xhr) {
            if (json && json.stats) {
                if (typeof json.stats.todayPO !== 'undefined') $('#stat_today_po').text(json.stats.todayPO + ' Pesanan');
                if (typeof json.stats.pendingPO !== 'undefined') $('#stat_pending_po').text(json.stats.pendingPO + ' Pesanan');
                if (typeof json.stats.preparingPO !== 'undefined') $('#stat_preparing_po').text(json.stats.preparingPO + ' Pesanan');
            }
        });

        // 🔍 HANDLER PENCARIAN REALTIME (PERSIS MANUAL ORDER: DEBOUNCE 400ms)
        let searchTimer;
        $('#filter_search').off('keyup').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                poTable.draw();
            }, 400);
        });

        $('#filter_date, #filter_status, #filter_payment_status').off('change').on('change', function() {
            poTable.draw();
        });

        $('#resetPoFilterBtn').off('click').on('click', function() {
            $('#filter_search').val('');
            $('#filter_date').val('');
            $('#filter_status').val('ALL');
            $('#filter_payment_status').val('ALL');
            poTable.draw();
        });

        // 🔄 AUTO POLLING DATATABLES REALTIME (SETIAP 5 DETIK - PERSIS MANUAL ORDER)
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
        }

        autoRefreshTimer = setInterval(function() {
            if (poTable && typeof poTable.ajax !== 'undefined') {
                let settings = poTable.settings()[0];
                if (settings.jqXHR && settings.jqXHR.readyState !== 4) return;

                let oldProcessing = settings.oFeatures.bProcessing;
                settings.oFeatures.bProcessing = false;

                poTable.ajax.reload(function() {
                    settings.oFeatures.bProcessing = oldProcessing;
                }, false);
            }
        }, 5000);
    }

    // 🔄 FUNGSI MENGEMBALIKAN URUTAN TABEL PO KE POSISI AWAL
    function resetPoTableOrder() {
        if (poTable) {
            poTable.order([[2, 'asc']]).draw();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPoDataTable);
    } else {
        initPoDataTable();
    }
</script>
@endsection