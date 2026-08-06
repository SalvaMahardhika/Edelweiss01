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

    {{-- 2. HEADER & FILTER JADWAL --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <form method="GET" action="{{ route('admin.po.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tanggal Pengambilan / Kirim</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Produksi</label>
                <select name="status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Status Aktif</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Preparing (Dipanggang)</option>
                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready (Siap)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Pembayaran</label>
                <select name="payment_status" class="w-full mt-1 px-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Pembayaran</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>DP (Ada Sisa Pelunasan)</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.po.index') }}" class="py-2 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- 3. TABEL UTAMA MANAJEMEN PO --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-5 py-4 w-1/4">No. Order & Pelanggan</th>
                        <th class="px-5 py-4 w-2/5">Item Pesanan (Kue)</th>
                        <th class="px-4 py-4 text-center">Jadwal Siap (`fulfill_at`)</th>
                        <th class="px-4 py-4 text-center">Status Pembayaran</th>
                        <th class="px-3 py-4 text-center w-36">Status Produksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    @forelse($orders as $order)
                    @php
                        $orderTypeVal = is_object($order->order_type) ? $order->order_type->value : $order->order_type;
                        $statusVal = is_object($order->status) ? $order->status->value : $order->status;
                        $paymentStatusVal = is_object($order->payment_status) ? $order->payment_status->value : $order->payment_status;
                        $paymentPlanVal = is_object($order->payment_plan) ? ($order->payment_plan->value ?? $order->payment_plan->name) : $order->payment_plan;
                        $isDpScheme = strtolower((string) $paymentPlanVal) === 'dp';
                    @endphp
                    <tr class="hover:bg-white/30 transition">
                        
                        {{-- No Order & Info Pembeli --}}
                        <td class="px-5 py-4">
                            <p class="font-black text-[#3e2723]">{{ $order->order_number }}</p>
                            <p class="text-xs font-bold text-gray-700 mt-0.5">{{ $order->customer_name }}</p>
                            <p class="text-[11px] text-gray-500"><i class="fa-solid fa-phone text-[9px] mr-1"></i>{{ $order->customer_phone }}</p>
                            
                            {{-- Badge Mode Order & Tombol Modal Alamat --}}
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
                        <td class="px-5 py-4">
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

                        {{-- Tanggal SIAP (Fulfill At) --}}
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($order->fulfill_at)
                                <p class="font-bold text-xs text-[#3e2723]">{{ \Carbon\Carbon::parse($order->fulfill_at)->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] font-semibold text-gray-500">{{ \Carbon\Carbon::parse($order->fulfill_at)->format('H:i') }} WIB</p>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum Diset</span>
                            @endif
                        </td>

                        {{-- Status Pembayaran --}}
                        <td class="px-4 py-4 text-center">
                            <div class="mb-1.5">
                                @if($isDpScheme)
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="fa-solid fa-pie-chart mr-1"></i> SKEMA DP
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                        <i class="fa-solid fa-credit-card mr-1"></i> FULL PAYMENT
                                    </span>
                                @endif
                            </div>

                            <div>
                                @if($paymentStatusVal === 'paid')
                                    <span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i> LUNAS 100%
                                    </span>
                                @elseif($paymentStatusVal === 'partial')
                                    <span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-xl bg-amber-100 text-amber-800 border border-amber-300 shadow-sm">
                                        <i class="fa-solid fa-pie-chart text-amber-600"></i> BAYAR DP
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-xl bg-rose-100 text-rose-800 border border-rose-300 shadow-sm">
                                        <i class="fa-solid fa-clock text-rose-600"></i> BELUM BAYAR
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1.5 space-y-0.5">
                                @if($paymentStatusVal === 'partial')
                                    <p class="text-[11px] font-bold text-amber-700">DP Terbayar: Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</p>
                                    <p class="text-[10px] font-black text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded-md inline-block">
                                        Sisa Bayar: Rp {{ number_format($order->total_amount - $order->amount_paid, 0, ',', '.') }}
                                    </p>
                                @elseif($paymentStatusVal === 'paid')
                                    <p class="text-[11px] font-bold text-emerald-700"><i class="fa-solid fa-check-circle mr-1"></i>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                @else
                                    <p class="text-[11px] font-bold text-red-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </td>

                        {{-- Status Pengerjaan (SEMUA GANTI STATUS MEMILIKI KONFIRMASI) --}}
                        <td class="px-3 py-4 text-center">
                            <form id="status-form-{{ $order->id }}" method="POST" action="{{ route('admin.po.updateStatus', $order->id) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" 
                                        onchange="handleStatusChange(this, '{{ $order->id }}', '{{ $order->order_number }}', '{{ $statusVal }}')" 
                                        class="w-full text-xs font-bold px-2 py-1.5 rounded-xl border border-white/50 shadow-md cursor-pointer transition focus:outline-none {{ $statusVal === 'completed' ? 'bg-emerald-600 text-white' : ($statusVal === 'preparing' ? 'bg-blue-600 text-white' : ($statusVal === 'ready' ? 'bg-purple-600 text-white' : ($statusVal === 'confirmed' ? 'bg-indigo-600 text-white' : ($statusVal === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white')))) }}">
                                    <option value="pending" class="bg-white text-gray-800" {{ $statusVal === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" class="bg-white text-gray-800" {{ $statusVal === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="preparing" class="bg-white text-gray-800" {{ $statusVal === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                    <option value="ready" class="bg-white text-gray-800" {{ $statusVal === 'ready' ? 'selected' : '' }}>Ready</option>
                                    <option value="completed" class="bg-white text-gray-800" {{ $statusVal === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                                    <option value="cancelled" class="bg-white text-gray-800" {{ $statusVal === 'cancelled' ? 'selected' : '' }}>Batal</option>
                                </select>
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                            <i class="fa-solid fa-calendar-xmark text-2xl mb-2 text-gray-400 block"></i>
                            Tidak ada jadwal pesanan PO aktif saat ini.
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

{{-- MODAL POPUP: KONFIRMASI UBAH STATUS (SEMUA PERUBAHAN STATUS PRODUKSI) --}}
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

    function openAddressModal(orderNumber, customerName, address) {
        document.getElementById('modalOrderInfo').innerText = `${orderNumber} - ${customerName}`;
        document.getElementById('modalAddressText').innerText = address && address.trim() !== '' ? address : 'Alamat pengiriman belum diisi / tidak tersedia.';
        document.getElementById('addressModal').classList.remove('hidden');
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
    }

    // Intercept dropdown status (Berlaku untuk SEMUA pilihan status)
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