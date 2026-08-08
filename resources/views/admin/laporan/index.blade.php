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

    {{-- 2. FILTER PERIODE & EXPORT BUTTONS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <form id="reportFilterForm" onsubmit="return false;" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tipe Pesanan</label>
                <select id="order_type" name="order_type" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Tipe</option>
                    <option value="pickup" {{ request('order_type') == 'pickup' ? 'selected' : '' }}>Pickup (Ambil Sendiri)</option>
                    <option value="delivery" {{ request('order_type') == 'delivery' ? 'selected' : '' }}>Delivery (Kirim)</option>
                </select>
            </div>

            <div class="flex gap-2 col-span-1 sm:col-span-3">
                <button type="button" id="btnResetReportFilter" class="py-2 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center flex items-center justify-center gap-1">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </button>
                
                {{-- TOMBOL EXPORT EXCEL --}}
                <a id="btnExportExcel" href="{{ route('admin.laporan.exportExcel', ['start_date' => $startDate, 'end_date' => $endDate, 'order_type' => request('order_type', 'ALL')]) }}" class="py-2 px-3 bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md hover:bg-emerald-800 transition flex items-center justify-center gap-1 ml-auto">
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

    {{-- 4. GRID 2 KOLOM: TOP PRODUK TERLARIS & RINCIAN PENJUALAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- KOLOM KIRI: TOP 5 PRODUK TERLARIS --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
            <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-crown text-amber-600"></i> Top 5 Produk Terlaris
            </h3>
            
            <div class="space-y-3" id="topProductsContainer">
                @forelse($topProducts as $index => $prod)
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/50 border border-white/60 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#3e2723] text-white font-bold text-xs flex items-center justify-center shrink-0">
                            #{{ $index + 1 }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#3e2723]">{{ $prod->product_name }}</p>
                            <p class="text-[10px] text-gray-500 font-semibold">{{ $prod->total_qty }} pcs terjual</p>
                        </div>
                    </div>
                    <span class="text-xs font-black text-amber-900">
                        Rp {{ number_format($prod->total_revenue, 0, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-gray-500 italic text-center py-4">Belum ada data penjualan pada periode ini.</p>
                @endforelse
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL RINCIAN TRANSAKSI (DATATABLES AJAX SERVER-SIDE) --}}
        <div class="lg:col-span-2 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden flex flex-col justify-between">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-700"></i> Rincian Transaksi Selesai
                </h3>

                {{-- INPUT SEARCH LIVE & TOMBOL RESET SORT --}}
                <div class="flex items-center gap-2">
                    <div class="relative min-w-[220px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="report_search" placeholder="Cari Nama / No HP..." class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                    </div>

                    <button type="button" onclick="resetTableSort()" title="Reset Urutan Tabel" class="p-1.5 px-2.5 bg-white/60 border border-white/50 rounded-xl text-xs font-bold text-[#3e2723] hover:bg-white transition flex items-center gap-1 shadow-sm shrink-0">
                        <i class="fa-solid fa-arrow-rotate-left text-amber-800"></i>
                        <span class="hidden sm:inline text-[11px]">Reset Sort</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 p-2 shadow-inner flex-1">
                <table id="reportTable" class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                            <th class="px-4 py-3 cursor-pointer">Tgl Selesai</th>
                            <th class="px-4 py-3 cursor-pointer">No. Order & Pelanggan</th>
                            <th class="px-4 py-3 text-center cursor-pointer">Tipe</th>
                            <th class="px-4 py-3 text-right cursor-pointer">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/30 text-xs font-medium">
                        {{-- Data dimuat dinamis via DataTables AJAX --}}
                    </tbody>
                </table>
            </div>
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
            container.html('<p class="text-xs text-gray-500 italic text-center py-4">Belum ada data penjualan pada periode ini.</p>');
            return;
        }

        let html = '';
        products.forEach((prod, index) => {
            const revenueFormatted = formatRupiah(prod.total_revenue);
            html += `
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/50 border border-white/60 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#3e2723] text-white font-bold text-xs flex items-center justify-center shrink-0">
                            #${index + 1}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#3e2723]">${prod.product_name}</p>
                            <p class="text-[10px] text-gray-500 font-semibold">${prod.total_qty} pcs terjual</p>
                        </div>
                    </div>
                    <span class="text-xs font-black text-amber-900">
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
                }
            },
            columns: [
                {
                    data: 'fulfill_at',
                    name: 'fulfill_at',
                    className: 'whitespace-nowrap',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'order_number',
                    name: 'order_number',
                    render: function(data, type, row) {
                        if (typeof data === 'string' && data.includes('<p')) {
                            return data;
                        }
                        return `<p class="font-bold text-[#3e2723]">${data || row.order_number}</p><p class="text-[11px] text-gray-600">${row.customer_name || ''}</p>`;
                    }
                },
                {
                    data: 'order_type',
                    name: 'order_type',
                    className: 'text-center uppercase',
                    render: function(data, type, row) {
                        let typeVal = row.order_type && typeof row.order_type === 'object' 
                            ? (row.order_type.value || row.order_type.name) 
                            : (data || row.order_type);
                        
                        typeVal = String(typeVal || 'pickup').toLowerCase();
                        const cls = typeVal === 'pickup' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800';
                        return `<span class="uppercase px-2 py-0.5 rounded-md text-[9px] font-bold ${cls}">${typeVal}</span>`;
                    }
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    className: 'text-right font-black text-[#3e2723]',
                    render: function(data, type, row) {
                        if (typeof data === 'string' && data.startsWith('Rp')) {
                            return data;
                        }
                        return formatRupiah(data || row.total_amount);
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

        // 🔍 HANDLER SEARCH LIVE REALTIME (DEBOUNCE 400ms - HANYA Nama & No HP)
        $('#report_search').off('keyup input').on('keyup input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                reportTable.draw(false);
            }, 400);
        });

        // 📅 HANDLER AUTO-FILTER INPUT TANGGAL & SELECT TIPE ORDER
        $('#start_date, #end_date, #order_type').off('change').on('change', function() {
            const params = $.param({
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                order_type: $('#order_type').val()
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
            $('#report_search').val('');

            const params = $.param({
                start_date: startOfMonth,
                end_date: today,
                order_type: 'ALL'
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