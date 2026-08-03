@extends('admin_layouts.master')

@section('page_title', 'History & Arsip Pesanan')

@section('content')
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- 1. RINGKASAN AMBIEN STATISTIK HISTORY --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        {{-- Total Riwayat --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Total Riwayat Pesanan</p>
                <h3 class="text-2xl font-black text-[#3e2723] mt-1">{{ $totalHistoryCount ?? 0 }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-box-archive font-bold"></i>
            </div>
        </div>

        {{-- Pesanan Selesai --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-800/60 uppercase tracking-wider">Pesanan Selesai</p>
                <h3 class="text-2xl font-black text-emerald-900 mt-1">{{ $completedCount ?? 0 }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-[#3e2723] fa-circle-check"></i>
            </div>
        </div>

        {{-- Pesanan Batal --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-red-800/60 uppercase tracking-wider">Pesanan Batal</p>
                <h3 class="text-2xl font-black text-red-900 mt-1">{{ $cancelledCount ?? 0 }} Pesanan</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-[#3e2723] fa-circle-xmark"></i>
            </div>
        </div>

        {{-- Total Omzet History --}}
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-800/60 uppercase tracking-wider">Total Nilai Selesai</p>
                <h3 class="text-2xl font-black text-amber-900 mt-1">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    {{-- 2. HEADER & FILTER HISTORY --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <form method="GET" action="{{ route('admin.orders.history') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
            
            {{-- Pencarian Kata Kunci --}}
            <div class="sm:col-span-2">
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Cari Order / Pelanggan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Order, Nama, HP, Email..." class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium placeholder-gray-400">
            </div>

            {{-- Tanggal Pemesanan (Placed At) --}}
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Pesan</label>
                <input type="date" name="placed_date" value="{{ request('placed_date') }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            {{-- Tanggal Pelaksanaan (Fulfill At) --}}
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Kirim/Ambil</label>
                <input type="date" name="fulfill_date" value="{{ request('fulfill_date') }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            {{-- Filter Status Akhir --}}
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Akhir</label>
                <select name="status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled (Batal)</option>
                </select>
            </div>

            {{-- Tombol Filter --}}
            <div class="flex gap-2 col-span-1">
                <button type="submit" class="flex-1 py-2 px-4 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.orders.history') }}" class="py-2 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- 3. TABEL UTAMA HISTORY PESANAN --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse">
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
                    @forelse($orders as $order)
                    @php
                        $orderTypeVal = is_object($order->order_type) ? $order->order_type->value : $order->order_type;
                        $statusVal = is_object($order->status) ? $order->status->value : $order->status;
                        $paymentStatusVal = is_object($order->payment_status) ? $order->payment_status->value : $order->payment_status;
                        $paymentPlanVal = is_object($order->payment_plan) ? $order->payment_plan->value : $order->payment_plan;
                        $placedAt = $order->placed_at ?? $order->created_at;
                    @endphp
                    <tr class="hover:bg-white/30 transition">
                        
                        {{-- Waktu Pemesanan --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-bold text-xs text-[#3e2723]">{{ \Carbon\Carbon::parse($placedAt)->translatedFormat('d M Y') }}</p>
                            <p class="text-[11px] font-semibold text-gray-500"><i class="fa-regular fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($placedAt)->format('H:i') }} WIB</p>
                        </td>

                        {{-- No Order & Info Pembeli --}}
                        <td class="px-6 py-4">
                            <p class="font-black text-[#3e2723]">{{ $order->order_number }}</p>
                            <p class="text-xs font-bold text-gray-700 mt-0.5">{{ $order->customer_name }}</p>
                            <p class="text-[11px] text-gray-500"><i class="fa-solid fa-phone text-[9px] mr-1"></i>{{ $order->customer_phone }}</p>
                            
                            {{-- Badge Mode Order & Tombol Lihat Alamat --}}
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

                        {{-- Tanggal Kirim / Ambil --}}
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($order->fulfill_at)
                                <p class="font-bold text-xs text-[#3e2723]">{{ \Carbon\Carbon::parse($order->fulfill_at)->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] font-semibold text-gray-500">{{ \Carbon\Carbon::parse($order->fulfill_at)->format('H:i') }} WIB</p>
                            @else
                                <span class="text-xs text-gray-400 italic">Tanpa Jadwal</span>
                            @endif
                        </td>

                        {{-- Total & Status Pembayaran --}}
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <p class="font-black text-[#3e2723]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <div class="mt-1">
                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $paymentStatusVal === 'paid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($paymentStatusVal === 'partial' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-red-100 text-red-800 border border-red-300') }}">
                                    {{ $paymentStatusVal === 'paid' ? 'LUNAS' : ($paymentStatusVal === 'partial' ? 'DP 50%' : 'BELUM BAYAR') }}
                                </span>
                            </div>
                        </td>

                        {{-- Status Akhir Pesanan --}}
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($statusVal === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-sm">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                </span>
                            @elseif($statusVal === 'cancelled')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-red-600 text-white shadow-sm">
                                    <i class="fa-solid fa-circle-xmark text-[10px]"></i> Batal
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-500 text-white shadow-sm">
                                    {{ strtoupper($statusVal) }}
                                </span>
                            @endif
                        </td>

                        {{-- Akses Detail Struk / Nota --}}
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <button type="button" 
                                    onclick="openReceiptModal({{ json_encode($order) }})" 
                                    class="p-2 rounded-xl bg-white/80 border border-white text-[#3e2723] hover:bg-[#3e2723] hover:text-white transition shadow-sm"
                                    title="Lihat Detail Struk Nota">
                                <i class="fa-solid fa-receipt text-base"></i>
                            </button>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">
                            <i class="fa-solid fa-box-open text-2xl mb-2 text-gray-400 block"></i>
                            Tidak ada data history pesanan yang ditemukan.
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
            <div class="flex justify-between text-gray-600"><span>Subtotal</span><span id="rcpSubtotal">Rp 0</span></div>
            <div class="flex justify-between text-gray-600"><span>Pajak / Biaya</span><span id="rcpTax">Rp 0</span></div>
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
    // Modal Alamat
    function openAddressModal(orderNumber, customerName, address) {
        document.getElementById('modalOrderInfo').innerText = `${orderNumber} - ${customerName}`;
        document.getElementById('modalAddressText').innerText = address && address.trim() !== '' ? address : 'Alamat pengiriman belum diisi / tidak tersedia.';
        document.getElementById('addressModal').classList.remove('hidden');
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
    }

    // Modal Struk / Nota
    function openReceiptModal(order) {
        document.getElementById('rcpOrderNumber').innerText = order.order_number;
        document.getElementById('rcpCustomerName').innerText = order.customer_name;
        document.getElementById('rcpCustomerPhone').innerText = order.customer_phone;
        
        const orderType = typeof order.order_type === 'object' ? order.order_type.value : order.order_type;
        document.getElementById('rcpOrderType').innerText = (orderType || 'pickup').toUpperCase();
        
        const placedAt = order.placed_at || order.created_at;
        document.getElementById('rcpPlacedAt').innerText = placedAt ? new Date(placedAt).toLocaleString('id-ID') : '-';

        // Render List Items
        let itemsHtml = '';
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                const price = parseFloat(item.unit_price || 0);
                const subtotal = parseFloat(item.subtotal || (price * item.quantity));
                itemsHtml += `
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-bold text-[#3e2723]">${item.quantity}x</span> ${item.product_name}
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