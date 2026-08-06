@extends('admin_layouts.master')

@section('page_title', 'Laporan Penjualan')

@section('content')
{{-- Include Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- 1. RINGKASAN AMBIEN KPI PENJUALAN (DENGAN CARD KETIGA / PENDING REALIZATION) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total Omzet --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-900/60 uppercase tracking-wider">Total Omzet</p>
                <h3 class="text-xl sm:text-2xl font-black text-amber-950 mt-1">Rp {{ number_format($totalOmzet ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-xl shadow-sm shrink-0">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>

        {{-- Total Pesanan Completed --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-800/60 uppercase tracking-wider">Pesanan Selesai</p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-900 mt-1">{{ $totalPesanan ?? 0 }} Transaksi</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-800 flex items-center justify-center text-xl shadow-sm shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        {{-- SARAN NO. 2: Pending Realization / Uang Gantung Dapur --}}
        @php
            $realtimeCash = $totalCashflowRealtime ?? ($totalOmzet ?? 0);
            $pendingRealization = max(0, $realtimeCash - ($totalOmzet ?? 0));
        @endphp
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-purple-900/60 uppercase tracking-wider" title="Uang masuk DP/Lunas dari PO yang belum diserahterimakan">Pending Realisasi</p>
                <h3 class="text-xl sm:text-2xl font-black text-purple-950 mt-1">Rp {{ number_format($pendingRealization, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-800 flex items-center justify-center text-xl shadow-sm shrink-0">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        {{-- Total Produk Terjual --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-blue-800/60 uppercase tracking-wider">Kue/Roti Terjual</p>
                <h3 class="text-xl sm:text-2xl font-black text-blue-900 mt-1">{{ $totalProdukTerjual ?? 0 }} Item</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-800 flex items-center justify-center text-xl shadow-sm shrink-0">
                <i class="fa-solid fa-bread-slice"></i>
            </div>
        </div>

        {{-- Rata-Rata Transaksi --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Rata-Rata Order</p>
                <h3 class="text-xl sm:text-2xl font-black text-[#3e2723] mt-1">Rp {{ number_format($avgOrderVal ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-xl shadow-sm shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>

    {{-- 2. FILTER PERIODE & EXPORT BUTTONS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tipe Pesanan</label>
                <select name="order_type" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Tipe</option>
                    <option value="pickup" {{ request('order_type') == 'pickup' ? 'selected' : '' }}>Pickup (Ambil Sendiri)</option>
                    <option value="delivery" {{ request('order_type') == 'delivery' ? 'selected' : '' }}>Delivery (Kirim)</option>
                </select>
            </div>

            <div class="flex gap-2 col-span-1 sm:col-span-3">
                <button type="submit" class="flex-1 py-2 px-3 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.laporan.index') }}" class="py-2 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center flex items-center justify-center">
                    Reset
                </a>
                
                {{-- TOMBOL EXPORT EXCEL --}}
                <a href="{{ route('admin.laporan.exportExcel', request()->query()) }}" class="py-2 px-3 bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md hover:bg-emerald-800 transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
            </div>
        </form>
    </div>

    {{-- 3. GRID UTAMA VISUALISASI GRAFIK & DONUT SKEMA PEMBAYARAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- SARAN NO. 1 & DUAL LINE CHART (KOLOM KIRI 2/3) --}}
        <div class="lg:col-span-2 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-chart-area text-amber-800"></i> Grafik Pendapatan Penjualan
                    </h3>
                    <p class="text-[11px] text-gray-500 font-semibold mt-0.5">Real-time Transfer vs Realisasi Pesanan Selesai</p>
                </div>

                {{-- SARAN NO. 1: Toggle Switch Tampilan Grafik (Harian / Mingguan / Bulanan) --}}
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

        {{-- SARAN NO. 3: BREAKDOWN SKEMA PEMBAYARAN (DONUT CHART - KOLOM KANAN 1/3) --}}
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
            
            <div class="space-y-3">
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

        {{-- KOLOM KANAN: TABEL RINCIAN TRANSAKSI --}}
        <div class="lg:col-span-2 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
            <h3 class="text-sm font-black text-[#3e2723] uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-emerald-700"></i> Rincian Transaksi Selesai
            </h3>

            <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                            <th class="px-4 py-3">Tgl Selesai</th>
                            <th class="px-4 py-3">No. Order & Pelanggan</th>
                            <th class="px-4 py-3 text-center">Tipe</th>
                            <th class="px-4 py-3 text-right">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/30 text-xs font-medium">
                        @forelse($completedOrders as $order)
                        <tr class="hover:bg-white/30 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-bold text-[#3e2723]">{{ \Carbon\Carbon::parse($order->fulfill_at ?? $order->created_at)->translatedFormat('d M Y') }}</p>
                                <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($order->fulfill_at ?? $order->created_at)->format('H:i') }} WIB</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-[#3e2723]">{{ $order->order_number }}</p>
                                <p class="text-[11px] text-gray-600">{{ $order->customer_name }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="uppercase px-2 py-0.5 rounded-md text-[9px] font-bold {{ $order->order_type == 'pickup' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $order->order_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-black text-[#3e2723]">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">
                                Tidak ada transaksi selesai pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $completedOrders->links() }}
            </div>
        </div>

    </div>
</div>

{{-- SCRIPT RENDERING CHART.JS --}}
<script>
    let mainRevenueChart = null;
    let currentGranularity = 'daily';

    // Data dari Backend Controller
    const rawDailyLabels   = @json($chartLabels ?? []);
    const rawDailyCashflow = @json($chartCashflow ?? $chartData ?? []);
    const rawDailyRealized = @json($chartRealized ?? $chartData ?? []);

    const dpCountVal   = @json($dpCount ?? 0);
    const fullCountVal = @json($fullCount ?? 0);

    // Function Agregasi Data untuk Toggle Harian, Mingguan, Bulanan
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

    // Function Switch Granularitas Tampilan Grafik
    function switchChartGranularity(type) {
        currentGranularity = type;

        ['daily', 'weekly', 'monthly'].forEach(mode => {
            const btn = document.getElementById(`btn-${mode}`);
            if (btn) {
                if (mode === type) {
                    btn.className = 'px-3 py-1 rounded-lg bg-[#3e2723] text-white transition';
                } else {
                    btn.className = 'px-3 py-1 rounded-lg hover:bg-white/80 transition';
                }
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

    document.addEventListener('DOMContentLoaded', function () {
        // 1. INITIALIZE DUAL LINE CHART (GRAFIK UTAMA)
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        
        mainRevenueChart = new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: rawDailyLabels.length > 0 ? rawDailyLabels : ['Tanpa Data'],
                datasets: [
                    {
                        label: 'Cashflow Real-Time (Uang Masuk / Transfer)',
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
                        label: 'Realisasi Omzet (Pesanan Selesai / Fulfill)',
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
                    legend: { 
                        display: true,
                        position: 'top',
                        labels: {
                            font: { size: 10, weight: 'bold' },
                            color: '#3e2723',
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
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
                            },
                            font: { size: 10, weight: 'bold' },
                            color: '#3e2723'
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.3)' }
                    },
                    x: {
                        ticks: { font: { size: 10, weight: 'bold' }, color: '#3e2723' },
                        grid: { display: false }
                    }
                }
            }
        });

        // 2. INITIALIZE DONUT CHART (SARAN NO. 3 - SKEMA PEMBAYARAN)
        const ctxPayment = document.getElementById('paymentSchemeChart').getContext('2d');
        new Chart(ctxPayment, {
            type: 'doughnut',
            data: {
                labels: ['Skema DP (50%)', 'Full Payment'],
                datasets: [{
                    data: [dpCountVal > 0 ? dpCountVal : 1, fullCountVal > 0 ? fullCountVal : 1],
                    backgroundColor: ['#d97706', '#10b981'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 10, weight: 'bold' },
                            color: '#3e2723',
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.parsed + ' Transaksi';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endsection