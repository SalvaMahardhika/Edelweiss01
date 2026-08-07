@extends('admin_layouts.master')

@section('page_title', 'History & Arsip Pesanan')

@section('content')
{{-- Style khusus untuk memastikan area scroll DataTables memiliki tinggi minimal cukup untuk 5+ baris --}}
<style>
    .dataTables_scrollBody {
        min-height: 380px !important;
        max-height: calc(100vh - 22rem) !important;
    }
</style>

{{-- Container Utama: Memungkinkan scroll halaman secara keseluruhan tanpa memotong komponen --}}
<div class="min-h-full flex flex-col space-y-6 pb-8 overflow-y-auto pr-2">

    {{-- 1. RINGKASAN AMBIEN STATISTIK HISTORY --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 flex-shrink-0">
        {{-- Total Riwayat --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Total Riwayat Pesanan</p>
                <h3 class="text-xl sm:text-2xl font-black text-[#3e2723] mt-1" id="stat-total-history">
                    {{ $totalHistoryCount ?? 0 }} Pesanan
                </h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-lg sm:text-xl shadow-sm">
                <i class="fa-solid fa-box-archive font-bold"></i>
            </div>
        </div>

        {{-- Pesanan Selesai --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-800/60 uppercase tracking-wider">Pesanan Selesai</p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-900 mt-1" id="stat-completed-count">
                    {{ $completedCount ?? 0 }} Pesanan
                </h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-500/10 text-emerald-800 flex items-center justify-center text-lg sm:text-xl shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-700"></i>
            </div>
        </div>

        {{-- Pesanan Batal --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-red-800/60 uppercase tracking-wider">Pesanan Batal</p>
                <h3 class="text-xl sm:text-2xl font-black text-red-900 mt-1" id="stat-cancelled-count">
                    {{ $cancelledCount ?? 0 }} Pesanan
                </h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-red-500/10 text-red-800 flex items-center justify-center text-lg sm:text-xl shadow-sm">
                <i class="fa-solid fa-circle-xmark text-red-700"></i>
            </div>
        </div>

        {{-- Total Omzet History --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-800/60 uppercase tracking-wider">Total Nilai Selesai</p>
                <h3 class="text-xl sm:text-2xl font-black text-amber-900 mt-1" id="stat-total-revenue">
                    Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-lg sm:text-xl shadow-sm">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    {{-- 2. HEADER & FILTER HISTORY (LIVE AUTOMATIC FILTER) --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 flex-shrink-0">
        <div class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
            
            {{-- Pencarian Kata Kunci --}}
            <div class="sm:col-span-2">
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Cari Order / Pelanggan</label>
                <input type="text" id="filter_search" value="{{ request('search') }}" placeholder="No. Order, Nama, HP, Email..." class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium placeholder-gray-400">
            </div>

            {{-- Tanggal Pemesanan (Placed At) --}}
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Pesan</label>
                <input type="date" id="filter_placed_date" value="{{ request('placed_date') }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            {{-- Tanggal Pelaksanaan (Fulfill At) --}}
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Kirim/Ambil</label>
                <input type="date" id="filter_fulfill_date" value="{{ request('fulfill_date') }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            {{-- Filter Status Akhir --}}
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Akhir</label>
                <select id="filter_status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Status</option>
                    <option value="completed">Completed (Selesai)</option>
                    <option value="cancelled">Cancelled (Batal)</option>
                </select>
            </div>

            {{-- Tombol Reset Filter --}}
            <div class="flex gap-2 col-span-1">
                <button type="button" id="btnResetFilter" class="w-full py-2 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- 3. TABEL UTAMA HISTORY PESANAN --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 flex-1 flex flex-col">
        {{-- Header Bar Atas Tabel: Tombol Reset Urutan Tabel --}}
        <div class="flex justify-between items-center mb-3 px-1">
            <span class="text-xs font-bold text-[#3e2723]/70 uppercase tracking-wider">
                <i class="fa-solid fa-list-check mr-1"></i> Data History Pesanan
            </span>
            <button type="button" id="btnResetSort" onclick="resetTableOrder()" class="px-3 py-1.5 bg-white/80 border border-white/60 hover:bg-[#3e2723] hover:text-white text-[#3e2723] text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-down-short-wide"></i> Reset Urutan Tabel
            </button>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner p-2 flex-1 flex flex-col">
            <table id="historyOrdersTable" class="w-full min-w-[800px] text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4">Waktu Pemesanan</th>
                        <th class="px-6 py-4">No. Order & Pelanggan</th>
                        <th class="px-6 py-4">Item Pesanan (Kue)</th>
                        <th class="px-6 py-4 text-center">Jadwal Kirim/Ambil</th>
                        <th class="px-6 py-4 text-center">Total & Pembayaran</th>
                        <th class="px-6 py-4 text-center">Status Akhir</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    {{-- Diisi secara dinamis oleh DataTables AJAX --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🚚 MODAL 1: ALAMAT PENGIRIMAN --}}
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

{{-- 🧾 MODAL 2: DETAIL STRUK / NOTA TRANSAKSI --}}
<div id="receiptModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-md p-6 rounded-[2rem] bg-white/60 backdrop-blur-3xl border border-white shadow-2xl relative space-y-4 my-auto max-h-[90vh] overflow-y-auto">
        
        <div class="text-center pb-3 border-b border-dashed border-[#3e2723]/30">
            <h2 class="text-lg font-black text-[#3e2723] tracking-wide uppercase">Edelweiss Bakery</h2>
            <p class="text-[11px] text-gray-600 font-medium">Kuitansi Pembelian Pre-Order</p>
            <p id="rcpOrderNumber" class="text-xs font-bold text-[#3e2723] mt-1">-</p>
        </div>

        {{-- Rincian Pelanggan --}}
        <div class="text-xs space-y-1 text-gray-700">
            <div class="flex justify-between"><span class="text-gray-500">Pelanggan:</span> <span id="rcpCustomerName" class="font-bold text-[#3e2723]">-</span></div>
            <div class="flex justify-between"><span class="text-gray-500">No. Telepon:</span> <span id="rcpCustomerPhone" class="font-medium">-</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Tipe Pesanan:</span> <span id="rcpOrderType" class="font-bold uppercase">-</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Tgl Pesan:</span> <span id="rcpPlacedAt" class="font-medium">-</span></div>
        </div>

        {{-- Tabel Items --}}
        <div class="border-t border-b border-dashed border-[#3e2723]/30 py-3 my-2">
            <p class="text-[10px] font-bold uppercase text-gray-500 mb-2">Item Rincian Kue:</p>
            <div id="rcpItemsList" class="space-y-1.5 text-xs">
                {{-- Dynamic via JS --}}
            </div>
        </div>

        {{-- Rincian Kalkulasi Harga --}}
        <div class="text-xs space-y-1.5 pt-1">
            <div class="flex justify-between text-[#3e2723]"><span>Subtotal</span><span id="rcpSubtotal">Rp 0</span></div>
            <div class="flex justify-between text-[#3e2723]"><span>Pajak / Biaya</span><span id="rcpTax">Rp 0</span></div>
            <div class="flex justify-between font-black text-sm text-[#3e2723] pt-1 border-t border-gray-200">
                <span>Total Belanja</span><span id="rcpTotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-800 font-bold"><span>Jumlah Dibayar</span><span id="rcpPaid">Rp 0</span></div>
            <div class="flex justify-between text-red-700 font-bold"><span>Sisa Tagihan</span><span id="rcpRemaining">Rp 0</span></div>
        </div>

        <div class="pt-3 flex gap-2">
            <button type="button" onclick="window.print()" class="flex-1 py-2.5 text-xs font-bold rounded-xl bg-amber-800 text-white hover:bg-amber-900 transition shadow-md flex items-center justify-center gap-1">
                <i class="fa-solid fa-print"></i> Cetak Struk
            </button>
            <button type="button" onclick="closeReceiptModal()" class="py-2.5 px-4 text-xs font-bold rounded-xl bg-gray-200 text-[#3e2723] hover:bg-gray-300 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    let historyOrderTable = null;
    let historyAutoRefreshTimer = null;

    function initHistoryOrdersDataTable() {
        if (typeof $ === 'undefined' || !$.fn.DataTable) {
            return;
        }

        $.fn.dataTable.ext.errMode = 'throw';

        historyOrderTable = $('#historyOrdersTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            autoWidth: false,
            dom: 'rtip', // Sembunyikan searchbox bawaan
            order: [[0, 'desc']], // Default urutkan dari Waktu Pemesanan Terbaru (Kolom Index 0)
            scrollY: 'calc(100vh - 22rem)', // Menyesuaikan area tabel
            scrollCollapse: true,
            ajax: {
                url: "{{ route('admin.orders.history') }}",
                type: "GET",
                global: false,
                data: function (d) {
                    d.search = $('#filter_search').val();
                    d.placed_date = $('#filter_placed_date').val();
                    d.fulfill_date = $('#filter_fulfill_date').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                {
                    data: 'placed_at',
                    name: 'placed_at',
                    orderable: true,
                    className: 'whitespace-nowrap',
                    render: function (data, type, row) {
                        return `
                            <p class="font-bold text-xs text-[#3e2723]">${row.placed_date_formatted || '-'}</p>
                            <p class="text-[11px] font-semibold text-gray-500"><i class="fa-regular fa-clock mr-1"></i>${row.placed_time_formatted || '-'} WIB</p>
                        `;
                    }
                },
                {
                    data: 'order_number',
                    name: 'order_number',
                    orderable: true,
                    render: function (data, type, row) {
                        let orderTypeVal = row.order_type && typeof row.order_type === 'object' ? (row.order_type.value || row.order_type.name) : row.order_type;
                        let isDelivery = String(orderTypeVal).toLowerCase() === 'delivery';

                        let badge = isDelivery
                            ? `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800"><i class="fa-solid fa-truck mr-1"></i> DELIVERY</span>`
                            : `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800"><i class="fa-solid fa-store mr-1"></i> PICKUP</span>`;

                        let addressBtn = isDelivery
                            ? `<button type="button" onclick="openAddressModal('${row.order_number}', '${escapeHtml(row.customer_name || '')}', '${escapeHtml(row.delivery_address || 'Alamat tidak tersedia')}')" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-white/80 hover:bg-blue-600 hover:text-white px-2 py-0.5 rounded-md border border-blue-200 shadow-sm transition"><i class="fa-solid fa-location-dot text-[9px]"></i> Lihat Alamat</button>`
                            : '';

                        return `
                            <div>
                                <p class="font-black text-[#3e2723]">${row.order_number || '-'}</p>
                                <p class="text-xs font-bold text-gray-700 mt-0.5">${escapeHtml(row.customer_name || 'Pelanggan')}</p>
                                <p class="text-[11px] text-gray-500"><i class="fa-solid fa-phone text-[9px] mr-1"></i>${row.customer_phone || '-'}</p>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    ${badge}
                                    ${addressBtn}
                                </div>
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
                        let itemList = '<ul class="space-y-1 text-xs">';
                        if (row.items && row.items.length > 0) {
                            row.items.forEach(function(item) {
                                itemList += `<li class="text-[#2d1f1b]"><span class="font-bold text-[#3e2723]">${item.quantity}x</span> ${escapeHtml(item.product_name || 'Item')}</li>`;
                            });
                        } else {
                            itemList += '<li class="text-gray-400 italic">Tidak ada rincian item</li>';
                        }
                        itemList += '</ul>';

                        if (row.notes) {
                            itemList += `<p class="text-[10px] text-amber-900 bg-amber-500/10 p-1.5 rounded-lg mt-2 italic border border-amber-500/20"><strong>Ket:</strong> "${escapeHtml(row.notes)}"</p>`;
                        }
                        return itemList;
                    }
                },
                {
                    data: 'fulfill_at',
                    name: 'fulfill_at',
                    orderable: true,
                    className: 'text-center whitespace-nowrap',
                    render: function (data, type, row) {
                        if (row.fulfill_date_formatted) {
                            return `
                                <p class="font-bold text-xs text-[#3e2723]">${row.fulfill_date_formatted}</p>
                                <p class="text-[11px] font-semibold text-gray-500">${row.fulfill_time_formatted} WIB</p>
                            `;
                        }
                        return `<span class="text-xs text-gray-400 italic">Tanpa Jadwal</span>`;
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    orderable: true,
                    className: 'text-center whitespace-nowrap',
                    render: function (data, type, row) {
                        let payStatus = row.payment_status && typeof row.payment_status === 'object' ? (row.payment_status.value || row.payment_status.name) : row.payment_status;
                        payStatus = String(payStatus || 'unpaid').toLowerCase();

                        let isPaid = ['paid', 'lunas'].includes(payStatus);
                        let isPartial = ['dp', 'partial'].includes(payStatus);

                        let payBadge = isPaid
                            ? `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">LUNAS</span>`
                            : (isPartial 
                                ? `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-300">DP 50%</span>`
                                : `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-red-100 text-red-800 border border-red-300">BELUM BAYAR</span>`);

                        let formattedTotal = typeof row.total_amount === 'number' ? 'Rp ' + row.total_amount.toLocaleString('id-ID') : (row.total_amount || 'Rp 0');

                        return `
                            <div>
                                <p class="font-black text-[#3e2723]">${formattedTotal}</p>
                                <div class="mt-1">${payBadge}</div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    className: 'text-center whitespace-nowrap',
                    render: function (data, type, row) {
                        let statusVal = row.status && typeof row.status === 'object' ? (row.status.value || row.status.name) : row.status;
                        statusVal = String(statusVal || 'completed').toLowerCase();

                        if (statusVal === 'completed') {
                            return `
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-sm">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                </span>
                            `;
                        } else if (statusVal === 'cancelled') {
                            return `
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-red-600 text-white shadow-sm">
                                    <i class="fa-solid fa-circle-xmark text-[10px]"></i> Batal
                                </span>
                            `;
                        }
                        return `
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-500 text-white shadow-sm">
                                ${statusVal.toUpperCase()}
                            </span>
                        `;
                    }
                },
                {
                    data: 'id',
                    name: 'action',
                    className: 'text-center whitespace-nowrap',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let orderDataEscaped = escapeHtml(JSON.stringify(row));
                        return `
                            <button type="button" 
                                    onclick="openReceiptModalFromData('${orderDataEscaped}')" 
                                    class="p-2 rounded-xl bg-white/80 border border-white text-[#3e2723] hover:bg-[#3e2723] hover:text-white transition shadow-sm"
                                    title="Lihat Detail Struk Nota">
                                <i class="fa-solid fa-receipt text-base"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: {
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ history pesanan",
                paginate: {
                    previous: "<i class='fa-solid fa-chevron-left'></i>",
                    next: "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        // ⚡ UPDATE REALTIME STATISTIK HISTORY SETIAP KALI AJAX DATATABLES MEMUAT DATA BARU
        $('#historyOrdersTable').off('xhr.dt').on('xhr.dt', function (e, settings, json, xhr) {
            if (json) {
                if (typeof json.totalHistoryCount !== 'undefined') {
                    $('#stat-total-history').text(json.totalHistoryCount + ' Pesanan');
                }
                if (typeof json.completedCount !== 'undefined') {
                    $('#stat-completed-count').text(json.completedCount + ' Pesanan');
                }
                if (typeof json.cancelledCount !== 'undefined') {
                    $('#stat-cancelled-count').text(json.cancelledCount + ' Pesanan');
                }
                if (typeof json.totalRevenue !== 'undefined') {
                    let formattedRevenue = typeof json.totalRevenue === 'number' ? json.totalRevenue.toLocaleString('id-ID') : json.totalRevenue;
                    $('#stat-total-revenue').text('Rp ' + formattedRevenue);
                }
            }
        });

        // ⚡ LIVE AUTO-FILTER LISTENERS
        let searchTimer;
        $('#filter_search').off('keyup').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                historyOrderTable.draw();
            }, 400);
        });

        $('#filter_placed_date, #filter_fulfill_date, #filter_status').off('change').on('change', function() {
            historyOrderTable.draw();
        });

        $('#btnResetFilter').off('click').on('click', function() {
            $('#filter_search').val('');
            $('#filter_placed_date').val('');
            $('#filter_fulfill_date').val('');
            $('#filter_status').val('ALL');
            historyOrderTable.draw();
        });

        // 🔄 AUTO POLLING DATATABLES REALTIME (SETIAP 5 DETIK - SILENT & TERKONTROL)
        if (historyAutoRefreshTimer) {
            clearInterval(historyAutoRefreshTimer);
        }

        historyAutoRefreshTimer = setInterval(function() {
            if (historyOrderTable && typeof historyOrderTable.ajax !== 'undefined') {
                let settings = historyOrderTable.settings()[0];
                if (settings.jqXHR && settings.jqXHR.readyState !== 4) {
                    return;
                }

                let oldProcessing = settings.oFeatures.bProcessing;
                settings.oFeatures.bProcessing = false;

                historyOrderTable.ajax.reload(function() {
                    settings.oFeatures.bProcessing = oldProcessing;
                }, false);
            }
        }, 5000); // 5 detik
    }

    // 🔄 FUNGSI MENGEMBALIKAN URUTAN TABEL KE POSISI AWAL (DESKTOP / MOBILE)
    function resetTableOrder() {
        if (historyOrderTable) {
            historyOrderTable.order([[0, 'desc']]).draw();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHistoryOrdersDataTable);
    } else {
        initHistoryOrdersDataTable();
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

    // Modal Alamat
    function openAddressModal(orderNumber, customerName, address) {
        document.getElementById('modalOrderInfo').innerText = `${orderNumber} - ${customerName}`;
        document.getElementById('modalAddressText').innerText = address && address.trim() !== '' ? address : 'Alamat pengiriman belum diisi / tidak tersedia.';
        document.getElementById('addressModal').classList.remove('hidden');
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
    }

    function openReceiptModalFromData(jsonString) {
        try {
            const order = JSON.parse(jsonString);
            openReceiptModal(order);
        } catch (e) {
            console.error('Gagal membaca data struk:', e);
        }
    }

    // Modal Struk / Nota
    function openReceiptModal(order) {
        document.getElementById('rcpOrderNumber').innerText = order.order_number;
        document.getElementById('rcpCustomerName').innerText = order.customer_name;
        document.getElementById('rcpCustomerPhone').innerText = order.customer_phone;
        
        const orderType = typeof order.order_type === 'object' ? order.order_type.value : order.order_type;
        document.getElementById('rcpOrderType').innerText = (orderType || 'pickup').toUpperCase();
        
        const placedAt = order.placed_at || order.created_at;
        document.getElementById('rcpPlacedAt').innerText = placedAt ? (order.placed_date_formatted + ' ' + order.placed_time_formatted + ' WIB') : '-';

        // Render List Items
        let itemsHtml = '';
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                const price = parseFloat(item.unit_price || 0);
                const subtotal = parseFloat(item.subtotal || (price * item.quantity));
                itemsHtml += `
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-bold text-[#3e2723]">${item.quantity}x</span> ${escapeHtml(item.product_name)}
                        </div>
                        <span class="font-semibold text-gray-700">Rp ${subtotal.toLocaleString('id-ID')}</span>
                    </div>
                `;
            });
        } else {
            itemsHtml = '<p class="text-gray-400 italic">Tidak ada rincian item.</p>';
        }
        document.getElementById('rcpItemsList').innerHTML = itemsHtml;

        // Render Totals
        const subtotal = parseFloat(order.subtotal || 0);
        const tax = parseFloat(order.tax_amount || 0);
        const total = parseFloat(order.total_amount || 0);
        const paid = parseFloat(order.amount_paid || 0);
        const remaining = Math.max(0, total - paid);

        document.getElementById('rcpSubtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('rcpTax').innerText = 'Rp ' + tax.toLocaleString('id-ID');
        document.getElementById('rcpTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('rcpPaid').innerText = 'Rp ' + paid.toLocaleString('id-ID');
        document.getElementById('rcpRemaining').innerText = 'Rp ' + remaining.toLocaleString('id-ID');

        document.getElementById('receiptModal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').classList.add('hidden');
    }
</script>
@endsection