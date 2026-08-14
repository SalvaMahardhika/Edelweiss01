@extends('admin_layouts.master')

@section('page_title', 'Laporan Penjualan')

@section('content')
{{-- Include Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dataTables_scrollBody {
        min-height: 280px !important;
        max-height: calc(100vh - 28rem) !important;
    }
</style>

<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- 1. RINGKASAN AMBIEN KPI PENJUALAN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total Omzet --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex flex-col justify-center">
            <p class="text-xs font-bold text-amber-900/60 uppercase tracking-wider">Total Omzet</p>
            <h3 id="kpi_total_omzet" class="text-xl sm:text-2xl font-black text-amber-950 mt-1">Rp {{ number_format($totalOmzet ?? 0, 0, ',', '.') }}</h3>
        </div>

        {{-- Total Pesanan Completed --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex flex-col justify-center">
            <p class="text-xs font-bold text-emerald-800/60 uppercase tracking-wider">Pesanan Selesai</p>
            <h3 id="kpi_total_pesanan" class="text-xl sm:text-2xl font-black text-emerald-900 mt-1">{{ $totalPesanan ?? 0 }} Transaksi</h3>
        </div>

        {{-- Pending Realization --}}
        @php
            $realtimeCash = $totalCashflowRealtime ?? ($totalOmzet ?? 0);
            $pendingRealization = max(0, $realtimeCash - ($totalOmzet ?? 0));
        @endphp
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex flex-col justify-center">
            <p class="text-xs font-bold text-purple-900/60 uppercase tracking-wider" title="Uang masuk DP/Lunas dari PO yang belum diserahterimakan">Pending Realisasi</p>
            <h3 id="kpi_pending_realization" class="text-xl sm:text-2xl font-black text-purple-950 mt-1">Rp {{ number_format($pendingRealization, 0, ',', '.') }}</h3>
        </div>

        {{-- Total Produk Terjual --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex flex-col justify-center">
            <p class="text-xs font-bold text-blue-800/60 uppercase tracking-wider">Kue/Roti Terjual</p>
            <h3 id="kpi_produk_terjual" class="text-xl sm:text-2xl font-black text-blue-900 mt-1">{{ $totalProdukTerjual ?? 0 }} Item</h3>
        </div>

        {{-- Rata-Rata Transaksi --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex flex-col justify-center">
            <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Rata-Rata Order</p>
            <h3 id="kpi_avg_order" class="text-xl sm:text-2xl font-black text-[#3e2723] mt-1">Rp {{ number_format($avgOrderVal ?? 0, 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- 2. FILTER PERIODE, TIPE PESANAN, SKEMA PEMBAYARAN & EXPORT BUTTONS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <form id="reportFilterForm" onsubmit="return false;" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tipe Pesanan</label>
                <select id="order_type" name="order_type" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Tipe</option>
                    <option value="pickup" {{ request('order_type') == 'pickup' ? 'selected' : '' }}>Pickup (Ambil Sendiri)</option>
                    <option value="delivery" {{ request('order_type') == 'delivery' ? 'selected' : '' }}>Delivery (Kirim)</option>
                </select>
            </div>

            {{-- FILTER SKEMA PEMBAYARAN REAL-TIME --}}
            <div class="lg:col-span-2">
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Skema Pembayaran</label>
                <select id="payment_scheme" name="payment_scheme" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Skema</option>
                    <option value="full">Full Payment</option>
                    <option value="dp">DP 50%</option>
                </select>
            </div>

            <div class="flex gap-2 col-span-1 sm:col-span-2 lg:col-span-8 mt-2 justify-end">
                <button type="button" id="btnResetReportFilter" class="py-2 px-4 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center flex items-center justify-center gap-1 shadow-sm">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </button>
                
                {{-- TOMBOL EXPORT EXCEL --}}
                <a id="btnExportExcel" href="{{ route('admin.laporan.exportExcel', ['start_date' => $startDate, 'end_date' => $endDate, 'order_type' => request('order_type', 'ALL'), 'payment_scheme' => 'ALL']) }}" class="py-2 px-4 bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md hover:bg-emerald-800 transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
            </div>
        </form>
    </div>

    {{-- 3. GRID UTAMA VISUALISASI GRAFIK & DONUT SKEMA PEMBAYARAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- DUAL LINE CHART --}}
        <div class="lg:col-span-2 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-chart-area text-amber-800"></i> Grafik Pendapatan Penjualan
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">Uang Diterima (Pemesanan) vs Realisasi Omzet (Tercapai Hari Serah Terima)</p>
                </div>

                <div class="flex items-center bg-white/60 p-1 rounded-xl border border-white/50 text-xs font-bold text-[#3e2723] shadow-sm">
                    <button type="button" onclick="switchChartGranularity('daily')" id="btn-daily" class="px-3 py-1 rounded-lg bg-[#3e2723] text-white transition">
                        Harian
                    </button>
                    <button type="button" onclick="switchChartGranularity('weekly')" id="btn-weekly" class="px-3 py-1 rounded-lg hover:bg-white/80 transition">
                        Mingguan
                    </button>
                    <button type="button" onclick="switchChartGranularity('monthly')" id="btn-monthly" class="px-3 py-1 rounded-lg hover:bg-white/80 transition">
                        Bulanan
                    </button>
                </div>
            </div>

            <div class="w-full h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        {{-- BREAKDOWN SKEMA PEMBAYARAN (DONUT CHART) --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-pie-chart text-purple-700"></i> Skema Pembayaran
                </h3>
                <p class="text-[11px] text-gray-500 font-semibold mb-4">DP 50% vs Full Payment</p>
            </div>

            <div class="w-full h-56 flex items-center justify-center my-auto">
                <canvas id="paymentSchemeChart"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-white/40 text-center">
                <div class="bg-white/40 p-2 rounded-xl border border-white/50">
                    <p class="text-[10px] text-gray-500 font-bold uppercase">Skema DP</p>
                    <p class="text-xs font-black text-amber-800" id="dp-count-text">{{ $dpCount ?? 0 }} Transaksi</p>
                </div>
                <div class="bg-white/40 p-2 rounded-xl border border-white/50">
                    <p class="text-[10px] text-gray-500 font-bold uppercase">Full Payment</p>
                    <p class="text-xs font-black text-emerald-800" id="full-count-text">{{ $fullCount ?? 0 }} Transaksi</p>
                </div>
            </div>
        </div>

    </div>

    {{-- 4. SECTION TOP 5 PRODUK TERLARIS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="fa-solid fa-crown text-amber-600"></i> Top 5 Produk Terlaris
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5 gap-3" id="topProductsContainer">
            @forelse($topProducts as $index => $prod)
            <div class="flex items-center justify-between p-3 rounded-2xl bg-white/50 border border-white/60 shadow-sm gap-2 min-w-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-xl bg-[#3e2723] text-white font-bold text-xs flex items-center justify-center shrink-0">
                        #{{ $index + 1 }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[#3e2723] truncate" title="{{ $prod->product_name }}">{{ $prod->product_name }}</p>
                        <p class="text-[10px] text-gray-500 font-semibold">{{ $prod->total_qty }} pcs terjual</p>
                    </div>
                </div>
                <span class="text-xs font-black text-amber-900 shrink-0 ml-1">
                    Rp {{ number_format($prod->total_revenue, 0, ',', '.') }}
                </span>
            </div>
            @empty
            <div class="col-span-full">
                <p class="text-xs text-gray-500 italic text-center py-4">Belum ada data penjualan pada periode ini.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- 5. SECTION FULL BARIS: TABEL RINCIAN TRANSAKSI (DATATABLES AJAX SERVER-SIDE) --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden flex flex-col justify-between">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-receipt text-emerald-700"></i> Rincian Transaksi Selesai
            </h3>

            {{-- INPUT SEARCH LIVE & TOMBOL RESET SORT --}}
            <div class="flex items-center gap-2">
                <div class="relative min-w-[240px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="report_search" placeholder="Cari Nama / No HP / Order..." class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                </div>

                <button type="button" onclick="resetTableSort()" title="Reset Urutan Tabel" class="p-1.5 px-2.5 bg-white/60 border border-white/50 rounded-xl text-xs font-bold text-[#3e2723] hover:bg-white transition flex items-center gap-1 shadow-sm shrink-0">
                    <i class="fa-solid fa-arrow-rotate-left text-amber-800"></i>
                    <span class="hidden sm:inline text-[11px]">Reset Sort</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 p-2 shadow-inner flex-1">
            <table id="reportTable" class="w-full text-left border-collapse min-w-[750px]">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-4 py-3 cursor-pointer">Waktu Transaksi</th>
                        <th class="px-4 py-3 cursor-pointer">Order & Pelanggan</th>
                        <th class="px-4 py-3 text-center cursor-pointer">Tipe & Alamat</th>
                        <th class="px-4 py-3 text-center cursor-pointer">Skema & Metode</th>
                        <th class="px-4 py-3 text-right cursor-pointer">Total Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-xs font-medium">
                    {{-- Data dimuat dinamis via DataTables AJAX --}}
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL POPUP QUICK VIEW DETAIL ALAMAT PENGIRIMAN & CATATAN PESANAN --}}
<div id="addressDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-md hidden">
    <div class="bg-white/95 border border-white/80 backdrop-blur-2xl rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <div class="flex items-center gap-2 text-blue-900">
                <i class="fa-solid fa-truck-ramp-box text-base"></i>
                <h4 class="text-sm font-black uppercase tracking-wider">Detail Pengiriman</h4>
            </div>
            <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase">No. Order & Pelanggan</span>
                <p id="modalOrderInfo" class="font-bold text-[#3e2723] text-xs mt-0.5"></p>
            </div>

            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase">Alamat Pengiriman</span>
                <p id="modalAddressText" class="p-3 bg-gray-50 border border-gray-200 rounded-xl font-semibold text-gray-800 whitespace-pre-line mt-1"></p>
            </div>

            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase">Catatan Pesanan</span>
                <p id="modalNotesText" class="p-2.5 bg-amber-500/10 border border-amber-400/30 rounded-xl font-medium text-amber-950 mt-1 italic"></p>
            </div>
        </div>

        <div class="pt-2">
            <button type="button" onclick="closeAddressModal()" class="w-full py-2.5 bg-[#3e2723] hover:bg-[#2c1b18] text-white text-xs font-bold rounded-xl shadow transition">
                Tutup Detail
            </button>
        </div>
    </div>
</div>

{{-- SCRIPT RENDERING CHART.JS & DATATABLES REALTIME AJAX --}}
<script>
    let reportTable = null;
    let mainRevenueChart = null;
    let paymentSchemeChart = null;
    let currentGranularity = 'daily';
    let searchTimer = null;
    let autoRefreshInterval = null;

    // Data Awal dari Backend Controller
    let rawDailyLabels    = @json($chartLabels ?? []);
    let rawDailyCashflow = @json($chartCashflow ?? $chartData ?? []);
    let rawDailyRealized = @json($chartRealized ?? $chartData ?? []);

    let dpCountVal   = @json($dpCount ?? 0);
    let fullCountVal = @json($fullCount ?? 0);

    // ⚡ PEMBULATAN MATEMATIS DENGAN Math.round AGAR TIDAK MENCETAK DESIMAL PANJANG
    function formatRupiah(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
    }

    function showAddressModal(orderNum, customerName, address, notes) {
        document.getElementById('modalOrderInfo').innerText = orderNum + ' - ' + customerName;
        document.getElementById('modalAddressText').innerText = address && address !== 'Alamat tidak diisi' ? address : 'Alamat tidak dicantumkan';
        document.getElementById('modalNotesText').innerText = notes && notes !== '-' ? notes : 'Tidak ada catatan khusus';
        document.getElementById('addressDetailModal').classList.remove('hidden');
    }

    function closeAddressModal() {
        document.getElementById('addressDetailModal').classList.add('hidden');
    }

    function aggregateData(labels, cashflow, realized, type) {
        if (type === 'daily' || labels.length === 0) {
            return { labels, cashflow, realized };
        }

        let aggregated = {};

        labels.forEach((label, idx) => {
            let key = label;
            if (type === 'weekly') {
                key = 'W-' + Math.ceil((idx + 1) / 7);
            } else if (type === 'monthly') {
                const parts = label.split(' ');
                key = parts.length > 1 ? parts[1] : label;
            }

            if (!aggregated[key]) {
                aggregated[key] = { cashflow: 0, realized: 0 };
            }

            aggregated[key].cashflow += (cashflow[idx] || 0);
            aggregated[key].realized += (realized[idx] || 0);
        });

        return {
            labels: Object.keys(aggregated),
            cashflow: Object.values(aggregated).map(item => item.cashflow),
            realized: Object.values(aggregated).map(item => item.realized)
        };
    }

    function switchChartGranularity(type) {
        currentGranularity = type;

        ['daily', 'weekly', 'monthly'].forEach(mode => {
            const btn = document.getElementById(`btn-${mode}`);
            if (btn) {
                btn.className = (mode === type) 
                    ? 'px-3 py-1 rounded-lg bg-[#3e2723] text-white transition' 
                    : 'px-3 py-1 rounded-lg hover:bg-white/80 transition';
            }
        });

        const agg = aggregateData(rawDailyLabels, rawDailyCashflow, rawDailyRealized, type);

        if (mainRevenueChart) {
            mainRevenueChart.data.labels = agg.labels.length > 0 ? agg.labels : ['Tanpa Data'];
            mainRevenueChart.data.datasets[0].data = agg.cashflow.length > 0 ? agg.cashflow : [0];
            mainRevenueChart.data.datasets[1].data = agg.realized.length > 0 ? agg.realized : [0];
            mainRevenueChart.update();
        }
    }

    // 🔄 FUNGSI RESET URUTAN TABEL (RESET SORT)
    function resetTableSort() {
        if (reportTable) {
            reportTable.order([[0, 'desc']]).draw();
        }
    }

    // 👑 RENDERING DYNAMIC TOP PRODUCTS
    function renderTopProducts(products) {
        const container = $('#topProductsContainer');
        if (!container.length) return;

        if (!products || products.length === 0) {
            container.html('<div class="col-span-full"><p class="text-xs text-gray-500 italic text-center py-4">Belum ada data penjualan pada periode ini.</p></div>');
            return;
        }

        let html = '';
        products.forEach((prod, index) => {
            const revenueFormatted = formatRupiah(prod.total_revenue);
            html += `
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/50 border border-white/60 shadow-sm gap-2 min-w-0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-xl bg-[#3e2723] text-white font-bold text-xs flex items-center justify-center shrink-0">
                            #${index + 1}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#3e2723] truncate" title="${prod.product_name}">${prod.product_name}</p>
                            <p class="text-[10px] text-gray-500 font-semibold">${prod.total_qty} pcs terjual</p>
                        </div>
                    </div>
                    <span class="text-xs font-black text-amber-900 shrink-0 ml-1">
                        ${revenueFormatted}
                    </span>
                </div>
            `;
        });

        container.html(html);
    }

    // 🍩 RENDERING DYNAMIC DONUT CHART
    function updateDonutChart(dp, full) {
        dpCountVal = dp || 0;
        fullCountVal = full || 0;

        $('#dp-count-text').text(dpCountVal + ' Transaksi');
        $('#full-count-text').text(fullCountVal + ' Transaksi');

        if (paymentSchemeChart) {
            const hasData = (dpCountVal > 0 || fullCountVal > 0);
            paymentSchemeChart.data.datasets[0].data = hasData ? [dpCountVal, fullCountVal] : [0, 0];
            paymentSchemeChart.update();
        }
    }

    function initReportDataTable() {
        if (typeof $ === 'undefined' || !$.fn.DataTable) return;

        $.fn.dataTable.ext.errMode = 'throw';

        // ⚡ REGISTRASI EVENT LISTENER 'xhr.dt' SEBELUM INIT DATATABLES (REALTIME ALL DATA UPDATE)
        $('#reportTable').off('xhr.dt').on('xhr.dt', function (e, settings, json, xhr) {
            if (json) {
                // 1. Update KPI Cards
                if (json.stats) {
                    $('#kpi_total_omzet').text(formatRupiah(json.stats.totalOmzet));
                    $('#kpi_total_pesanan').text((json.stats.totalPesanan || 0) + ' Transaksi');
                    $('#kpi_pending_realization').text(formatRupiah(json.stats.pendingRealization));
                    $('#kpi_produk_terjual').text((json.stats.totalProdukTerjual || 0) + ' Item');
                    $('#kpi_avg_order').text(formatRupiah(json.stats.avgOrderVal));

                    // Update Donut Chart
                    updateDonutChart(json.stats.dpCount, json.stats.fullCount);
                }

                // 2. Update Top 5 Products
                if (json.topProducts) {
                    renderTopProducts(json.topProducts);
                }

                // 3. Update Chart Datasets Realtime
                if (json.chart) {
                    rawDailyLabels = json.chart.labels || [];
                    rawDailyCashflow = json.chart.cashflow || [];
                    rawDailyRealized = json.chart.realized || [];

                    switchChartGranularity(currentGranularity);
                }
            }
        });

        reportTable = $('#reportTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            autoWidth: false,
            dom: 'rtip',
            scrollY: 'calc(100vh - 28rem)',
            scrollCollapse: true,
            order: [[0, 'desc']],
            ajax: {
                url: "{{ route('admin.laporan.index') }}",
                type: "GET",
                global: false,
                data: function (d) {
                    d.search = $('#report_search').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.order_type = $('#order_type').val();
                    d.payment_scheme = $('#payment_scheme').val(); // Menikung filter skema ke backend
                }
            },
            columns: [
                {
                    data: 'fulfill_at',
                    name: 'fulfill_at',
                    className: 'align-top whitespace-nowrap',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'order_number',
                    name: 'order_number',
                    className: 'align-top',
                    render: function(data, type, row) {
                        let orderNum = row.order_number || '';
                        let custName = row.customer_name || '';
                        let custPhone = row.customer_phone || '';

                        return `
                            <div>
                                <p class="font-black text-[#3e2723] text-xs">${orderNum}</p>
                                <p class="text-xs font-bold text-gray-800">${custName} <span class="text-[10px] font-normal text-gray-500">(${custPhone})</span></p>
                            </div>
                        `;
                    }
                },
                {
                    data: 'order_type',
                    name: 'order_type',
                    className: 'align-top text-center',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'payment_scheme',
                    name: 'payment_scheme',
                    className: 'align-top text-center sm:text-left',
                    orderable: false,
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    className: 'align-top text-right',
                    render: function(data, type, row) {
                        return data || formatRupiah(row.total_amount);
                    }
                }
            ],
            language: {
                search: "",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                paginate: {
                    previous: "<i class='fa-solid fa-chevron-left'></i>",
                    next: "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        // 🔍 HANDLER SEARCH LIVE REALTIME (DEBOUNCE 400ms - Nama, No HP, & No Order)
        $('#report_search').off('keyup input').on('keyup input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                reportTable.draw(false);
            }, 400);
        });

        // 📅 HANDLER AUTO-FILTER INPUT TANGGAL, SELECT TIPE ORDER, & SKEMA PEMBAYARAN
        $('#start_date, #end_date, #order_type, #payment_scheme').off('change').on('change', function() {
            const params = $.param({
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                order_type: $('#order_type').val(),
                payment_scheme: $('#payment_scheme').val()
            });
            $('#btnExportExcel').attr('href', "{{ route('admin.laporan.exportExcel') }}?" + params);

            reportTable.draw();
        });

        // 🔄 HANDLER RESET FILTER LAPORAN
        $('#btnResetReportFilter').off('click').on('click', function() {
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');

            const startOfMonth = `${yyyy}-${mm}-01`;
            const today = `${yyyy}-${mm}-${dd}`;

            $('#start_date').val(startOfMonth);
            $('#end_date').val(today);
            $('#order_type').val('ALL');
            $('#payment_scheme').val('ALL');
            $('#report_search').val('');

            const params = $.param({
                start_date: startOfMonth,
                end_date: today,
                order_type: 'ALL',
                payment_scheme: 'ALL'
            });
            $('#btnExportExcel').attr('href', "{{ route('admin.laporan.exportExcel') }}?" + params);

            reportTable.draw();
        });

        // ⚡ AUTO-POLLING INTERVAL (Setiap 12 Detik Refresh Data Otomatis Secara Background)
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(function() {
            if (reportTable && document.visibilityState === 'visible') {
                reportTable.ajax.reload(null, false);
            }
        }, 12000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initReportDataTable();

        // 1. INITIALIZE DUAL LINE CHART (GRAFIK UTAMA)
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        mainRevenueChart = new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: rawDailyLabels.length > 0 ? rawDailyLabels : ['Tanpa Data'],
                datasets: [
                    {
                        label: 'Uang Diterima / Cashflow Real-Time',
                        data: rawDailyCashflow.length > 0 ? rawDailyCashflow : [0],
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#d97706',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Realisasi Omzet (Hari Pengambilan Tiba)',
                        data: rawDailyRealized.length > 0 ? rawDailyRealized : [0],
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.05)',
                        borderWidth: 3,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.35,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(context.parsed.y || 0));
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. INITIALIZE DONUT CHART
        const ctxPayment = document.getElementById('paymentSchemeChart').getContext('2d');
        const hasPaymentData = (dpCountVal > 0 || fullCountVal > 0);

        paymentSchemeChart = new Chart(ctxPayment, {
            type: 'doughnut',
            data: {
                labels: ['Skema DP (50%)', 'Full Payment'],
                datasets: [{
                    data: hasPaymentData ? [dpCountVal, fullCountVal] : [0, 0],
                    backgroundColor: ['#d97706', '#10b981'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%'
            }
        });
    });
</script>
@endsection