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

    {{-- 2. HEADER & LIVE AUTO-FILTER (TANPA RELOAD HALAMAN) --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Pengambilan / Kirim</label>
                <input type="date" id="filter_date" value="{{ request('date') }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Produksi</label>
                <select id="filter_status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Status Aktif</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="preparing">Preparing (Dipanggang)</option>
                    <option value="ready">Ready (Siap)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Pembayaran</label>
                <select id="filter_payment_status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Pembayaran</option>
                    <option value="unpaid">Belum Bayar</option>
                    <option value="partial">DP (Ada Sisa Pelunasan)</option>
                    <option value="paid">Lunas</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="button" id="resetFilterBtn" class="w-full py-2 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center shadow-sm">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- 3. TABEL UTAMA MANAJEMEN PO (DATATABLES AJAX) --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 p-2 shadow-inner">
            <table id="poTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-5 py-4">No. Order & Pelanggan</th>
                        <th class="px-5 py-4">Item Pesanan (Kue)</th>
                        <th class="px-4 py-4 text-center">Jadwal Siap (`fulfill_at`)</th>
                        <th class="px-4 py-4 text-center">Status Pembayaran</th>
                        <th class="px-3 py-4 text-center w-36">Status Produksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    {{-- Data dimuat dinamis via AJAX DataTables --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL POPUP: ALAMAT PENGIRIMAN --}}
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

{{-- MODAL POPUP: KONFIRMASI UBAH STATUS --}}
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

    $(document).ready(function() {
        // 1. INISIALISASI DATATABLES AJAX
        let table = $('#poTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.po.index') }}",
                data: function (d) {
                    d.date = $('#filter_date').val();
                    d.status = $('#filter_status').val();
                    d.payment_status = $('#filter_payment_status').val();
                }
            },
            columns: [
                {
                    data: null,
                    name: 'order_number',
                    render: function (data, type, row) {
                        let orderTypeVal = row.order_type && typeof row.order_type === 'object' ? row.order_type.value : row.order_type;
                        let isPickup = orderTypeVal === 'pickup';
                        let typeBadge = isPickup 
                            ? `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800"><i class="fa-solid fa-store mr-1"></i> PICKUP</span>`
                            : `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800"><i class="fa-solid fa-truck mr-1"></i> DELIVERY</span>`;

                        let addressBtn = (!isPickup && row.delivery_address) 
                            ? `<button type="button" onclick="openAddressModal('${row.order_number}', '${escapeHtml(row.customer_name)}', '${escapeHtml(row.delivery_address)}')" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-white/80 hover:bg-blue-600 hover:text-white px-2 py-0.5 rounded-md border border-blue-200 shadow-sm transition"><i class="fa-solid fa-location-dot text-[9px]"></i> Lihat Alamat</button>`
                            : '';

                        return `
                            <div>
                                <p class="font-black text-[#3e2723]">${row.order_number}</p>
                                <p class="text-xs font-bold text-gray-700 mt-0.5">${row.customer_name}</p>
                                <p class="text-[11px] text-gray-500"><i class="fa-solid fa-phone text-[9px] mr-1"></i>${row.customer_phone || '-'}</p>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    ${typeBadge}
                                    ${addressBtn}
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: null,
                    name: 'items',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        let itemList = '<ul class="space-y-1 text-xs">';
                        if (row.items && row.items.length > 0) {
                            row.items.forEach(function(item) {
                                itemList += `<li class="text-[#2d1f1b]"><span class="font-bold text-[#3e2723]">${item.quantity}x</span> ${item.product_name}</li>`;
                            });
                        } else {
                            itemList += '<li class="text-gray-400 italic">Tidak ada item</li>';
                        }
                        itemList += '</ul>';

                        if (row.notes) {
                            itemList += `<p class="text-[10px] text-amber-900 bg-amber-500/10 p-1.5 rounded-lg mt-2 italic border border-amber-500/20"><strong>Ket:</strong> "${row.notes}"</p>`;
                        }
                        return itemList;
                    }
                },
                {
                    data: 'fulfill_at',
                    name: 'fulfill_at',
                    className: 'text-center whitespace-nowrap',
                    render: function (data) {
                        if (data) {
                            return `<p class="font-bold text-xs text-[#3e2723]">${data}</p>`;
                        }
                        return `<span class="text-xs text-gray-400 italic">Belum Diset</span>`;
                    }
                },
                {
                    data: null,
                    name: 'payment_status',
                    className: 'text-center',
                    render: function (data, type, row) {
                        let payPlan = row.payment_plan && typeof row.payment_plan === 'object' ? (row.payment_plan.value || row.payment_plan.name) : row.payment_plan;
                        let isDp = String(payPlan).toLowerCase() === 'dp';
                        let payStatus = row.payment_status && typeof row.payment_status === 'object' ? row.payment_status.value : row.payment_status;

                        let schemeBadge = isDp 
                            ? `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fa-solid fa-pie-chart mr-1"></i> SKEMA DP</span>`
                            : `<span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200"><i class="fa-solid fa-credit-card mr-1"></i> FULL PAYMENT</span>`;

                        let statusBadge = '';
                        let amountText = '';

                        if (payStatus === 'paid') {
                            statusBadge = `<span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm"><i class="fa-solid fa-circle-check text-emerald-600"></i> LUNAS 100%</span>`;
                            amountText = `<p class="text-[11px] font-bold text-emerald-700"><i class="fa-solid fa-check-circle mr-1"></i>${row.total_amount}</p>`;
                        } else if (payStatus === 'partial') {
                            statusBadge = `<span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-xl bg-amber-100 text-amber-800 border border-amber-300 shadow-sm"><i class="fa-solid fa-pie-chart text-amber-600"></i> BAYAR DP</span>`;
                            let paidVal = row.amount_paid ? 'Rp ' + new Intl.NumberFormat('id-ID').format(row.amount_paid) : 'Rp 0';
                            amountText = `<p class="text-[11px] font-bold text-amber-700">DP Terbayar: ${paidVal}</p>`;
                        } else {
                            statusBadge = `<span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-xl bg-rose-100 text-rose-800 border border-rose-300 shadow-sm"><i class="fa-solid fa-clock text-rose-600"></i> BELUM BAYAR</span>`;
                            amountText = `<p class="text-[11px] font-bold text-red-700">${row.total_amount}</p>`;
                        }

                        return `
                            <div class="mb-1.5">${schemeBadge}</div>
                            <div>${statusBadge}</div>
                            <div class="mt-1.5 space-y-0.5">${amountText}</div>
                        `;
                    }
                },
                {
                    data: null,
                    name: 'status',
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        let statusVal = row.status && typeof row.status === 'object' ? row.status.value : row.status;
                        
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
                search: "Cari Pesanan:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pesanan",
                paginate: {
                    previous: "<i class='fa-solid fa-chevron-left'></i>",
                    next: "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        // 2. TRIGGER LIVE AUTO-FILTER
        $('#filter_date, #filter_status, #filter_payment_status').on('change', function() {
            table.draw();
        });

        // 3. RESET FILTER BUTTON
        $('#resetFilterBtn').on('click', function() {
            $('#filter_date').val('');
            $('#filter_status').val('ALL');
            $('#filter_payment_status').val('ALL');
            table.draw();
        });
    });

    // Helper ESCAPE HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Modal Alamat Pengiriman
    function openAddressModal(orderNumber, customerName, address) {
        document.getElementById('modalOrderInfo').innerText = `${orderNumber} - ${customerName}`;
        document.getElementById('modalAddressText').innerText = address && address.trim() !== '' ? address : 'Alamat pengiriman belum diisi / tidak tersedia.';
        document.getElementById('addressModal').classList.remove('hidden');
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
    }

    // Intercept Modal Konfirmasi Status Produksi
    function handleStatusChange(selectElement, orderId, orderNumber, currentStatus) {
        const selectedVal = selectElement.value;

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