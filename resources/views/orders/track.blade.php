<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya | Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { backdrop-filter: blur(20px); background: rgba(255, 255, 255, 0.5); }
    </style>
</head>
<body class="bg-gradient-to-br from-[#fafafa] via-[#f5efe8] to-[#ede5dc] text-[#3e2723] min-h-screen pt-28 pb-16">

@include('layouts.navbar')

<main class="max-w-3xl mx-auto px-4 space-y-6">

    {{-- FLASHDATA NOTIFIKASI SUKSES UPLOAD BUKTI --}}
    @if(session('success'))
    <div class="glass border border-emerald-500/40 bg-emerald-500/10 rounded-2xl p-4 shadow-lg flex items-center justify-between text-xs font-bold text-emerald-900">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    {{-- 1. BOX PENCARIAN NOMOR HP --}}
    <div class="glass border border-white/60 rounded-[2.5rem] p-6 shadow-xl text-center space-y-3">
        <h1 class="text-2xl font-black text-[#3e2723]">Lacak Pesanan Saya</h1>
        <p class="text-xs text-gray-500 max-w-md mx-auto">Masukkan nomor Telepon / WhatsApp Anda yang terdaftar saat melakukan Pre-Order untuk verifikasi data.</p>

        <form method="GET" action="{{ route('orders.track') }}" class="flex gap-2 max-w-md mx-auto pt-2">
            <div class="relative flex-1">
                <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="tel" name="search" value="{{ $search }}" placeholder="Contoh: 08123456789..." required class="w-full pl-11 pr-4 py-3 text-sm rounded-2xl bg-white/80 border border-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-semibold text-[#3e2723] shadow-inner">
            </div>
            <button type="submit" class="px-6 py-3 bg-[#3e2723] hover:bg-[#2c1b18] text-white font-bold text-sm rounded-2xl shadow-lg transition duration-300">
                Cari Pesanan
            </button>
        </form>
    </div>

    {{-- 2. DAFTAR LIST PESANAN PENGGUNA --}}
    @if(isset($orders) && $orders->count() > 0)
        <div class="space-y-4">
            <div class="flex justify-between items-center px-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#3e2723]/70">Daftar Pesanan Ditemukan ({{ $orders->count() }})</h2>
            </div>

            @foreach($orders as $order)
                @php
                    $statusVal = is_object($order->status) ? ($order->status->value ?? (string) $order->status) : (string) $order->status;
                    $paymentStatusVal = is_object($order->payment_status) ? ($order->payment_status->value ?? (string) $order->payment_status) : (string) $order->payment_status;
                    $orderTypeVal = is_object($order->order_type) ? ($order->order_type->value ?? (string) $order->order_type) : (string) $order->order_type;
                    $paymentMethodVal = is_object($order->payment_method) ? ($order->payment_method->value ?? (string) $order->payment_method) : (string) $order->payment_method;
                    $paymentPlanVal = is_object($order->payment_plan) ? ($order->payment_plan->value ?? $order->payment_plan->name) : (string) $order->payment_plan;

                    $sisaTagihan = $order->total_amount - $order->amount_paid;
                    $itemNames = $order->items->pluck('product_name')->join(', ');

                    // Pengecekan apakah transaksi ini skema DP dan sudah dibayar DP-nya (partial)
                    $isDpScheme = strtolower((string) $paymentPlanVal) === 'dp';
                    $isDpAndPartial = $isDpScheme && ($paymentStatusVal === 'partial');

                    // Menentukan apakah order masih butuh aksi pembayaran online / unggah bukti (TIDAK berlaku untuk skema DP yang sudah partial)
                    $canPayOrUpload = ($sisaTagihan > 0) && !in_array($statusVal, ['completed', 'cancelled']) && ($paymentStatusVal !== 'paid') && !$isDpAndPartial;

                    // MENGAMBIL RIWAYAT FOTO BUKTI TRANSFER DARI VIRTUAL ACCESSOR MODEL ORDER
                    $historyFormatted = $order->payment_proof_history ?? [];

                    $proofUrl = $order->latest_payment_proof_url;
                    if (empty($proofUrl) && !empty($historyFormatted)) {
                        $latest = end($historyFormatted);
                        $proofUrl = $latest['url'] ?? '';
                    }

                    $hasProofFile = !empty($proofUrl);

                    // 🛑 LIMITASI FRONTEND: MAKSIMAL 5X UPLOAD FOTO BUKTI
                    $uploadCount = count($historyFormatted);
                    $isLimitReached = $uploadCount >= 5;
                @endphp

                <div class="glass border border-white/60 rounded-[2rem] p-5 shadow-xl transition hover:shadow-2xl space-y-4">
                    {{-- Header Card --}}
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 pb-3 border-b border-[#3e2723]/10">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">No. Order: {{ $order->order_number }}</span>
                                
                                {{-- BADGE METODE PEMBAYARAN --}}
                                @if($paymentMethodVal === 'manual_wa')
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-brands fa-whatsapp mr-1"></i> Manual WA
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase bg-purple-100 text-purple-800 border border-purple-300">
                                        <i class="fa-solid fa-bolt mr-1"></i> PG DOKU
                                    </span>
                                @endif
                            </div>
                            
                            <h3 class="text-base font-black text-[#3e2723] mt-0.5">{{ $order->customer_name }}</h3>
                            <p class="text-[11px] text-gray-500"><i class="fa-regular fa-calendar mr-1"></i> Dipesan: {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }} WIB</p>
                        </div>

                        <div class="flex items-center gap-1.5 flex-wrap">
                            {{-- Status Pembayaran Badge --}}
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider {{ $paymentStatusVal === 'paid' ? 'bg-emerald-500/15 text-emerald-800 border border-emerald-500/30' : ($paymentStatusVal === 'partial' ? 'bg-amber-500/15 text-amber-800 border border-amber-500/30' : 'bg-rose-500/15 text-rose-800 border border-rose-500/30') }}">
                                {{ $paymentStatusVal }}
                            </span>

                            {{-- Status Dapur --}}
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider bg-[#3e2723] text-white">
                                {{ $statusVal }}
                            </span>
                        </div>
                    </div>

                    {{-- Body Ringkasan Kue & Jadwal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-white/40 p-3 rounded-xl border border-white/50">
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Ringkasan Pesanan</p>
                            <p class="font-bold text-[#3e2723] mt-0.5 truncate">
                                {{ $itemNames }}
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Total {{ $order->items->sum('quantity') }} Pcs Kue</p>
                        </div>

                        <div class="bg-white/40 p-3 rounded-xl border border-white/50">
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Jadwal Siap (`fulfill_at`)</p>
                            <p class="font-bold text-[#3e2723] mt-0.5">
                                <i class="fa-regular fa-clock text-amber-700 mr-1"></i>
                                {{ $order->fulfill_at ? \Carbon\Carbon::parse($order->fulfill_at)->translatedFormat('d M Y, H:i') . ' WIB' : 'Menunggu Konfirmasi' }}
                            </p>
                        </div>
                    </div>

                    {{-- PESAN INSTRUKSI PELUNASAN KHUSUS STATUS DP (PARTIAL) --}}
                    @if($isDpAndPartial && !in_array($statusVal, ['completed', 'cancelled']))
                        <div class="p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl text-xs text-amber-900 flex items-start gap-2.5 shadow-sm">
                            <i class="fa-solid fa-circle-info text-amber-700 text-sm mt-0.5"></i>
                            <div>
                                <p class="font-bold">DP Berhasil Diterima</p>
                                <p class="text-[11px] mt-0.5 text-amber-800 leading-relaxed">
                                    Mohon lakukan pelunasan sisa tagihan sebesar <strong class="text-rose-700 font-extrabold">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong> saat pesanan {{ $orderTypeVal === 'pickup' ? 'diambil di toko' : 'diantarkan ke lokasi Anda' }}.
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Footer Card & Tombol Aksi --}}
                    <div class="flex items-center justify-between pt-1 gap-2 flex-wrap">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Total / Sisa Pelunasan</p>
                            <p class="text-sm font-black text-[#3e2723]">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                @if($sisaTagihan > 0 && !in_array($statusVal, ['completed', 'cancelled']) && $paymentStatusVal !== 'paid')
                                    <span class="text-xs text-rose-600 font-bold ml-1">(Sisa: Rp {{ number_format($sisaTagihan, 0, ',', '.') }})</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Tombol Buka Modal Detail --}}
                            <button type="button" onclick="showOrderDetail('{{ $order->id }}')" class="px-3.5 py-2 bg-white/80 hover:bg-white text-[#3e2723] font-bold text-xs rounded-xl border border-white/80 shadow-sm transition">
                                <i class="fa-solid fa-eye mr-1"></i> Detail
                            </button>

                            {{-- LOGIKA TOMBOL BUKTI TRANSFER & PEMBAYARAN MANUAL DENGAN BATAS 5X --}}
                            @if($canPayOrUpload)
                                @if($paymentMethodVal === 'manual_wa')
                                    @if($hasProofFile)
                                        {{-- 👁️ 1. TOMBOL LIHAT BUKTI SAYA --}}
                                        <button type="button" 
                                                onclick="openViewProofModal('{{ $proofUrl }}', '{{ $order->order_number }}', {{ json_encode($historyFormatted) }})" 
                                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1">
                                            <i class="fa-solid fa-image"></i> Lihat Bukti Saya
                                        </button>

                                        {{-- 🔄 2. TOMBOL GANTI BUKTI TRANSFER (DILIMIT MAKSIMAL 5X) --}}
                                        @if($isLimitReached)
                                            <button type="button" disabled 
                                                    title="Batas maksimal 5x upload foto telah tercapai." 
                                                    class="px-3 py-2 bg-gray-400 text-white font-bold text-xs rounded-xl shadow-none cursor-not-allowed flex items-center gap-1">
                                                <i class="fa-solid fa-lock"></i> Max 5x Upload
                                            </button>
                                        @else
                                            <button type="button" 
                                                    onclick="openUploadProofModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ number_format($sisaTagihan, 0, ',', '.') }}', true)" 
                                                    class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1">
                                                <i class="fa-solid fa-arrows-rotate"></i> Ganti Gambar ({{ $uploadCount }}/5)
                                            </button>
                                        @endif
                                    @else
                                        {{-- 📥 3. TOMBOL INPUT BUKTI PERTAMA KALI --}}
                                        <button type="button" 
                                                onclick="openUploadProofModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ number_format($sisaTagihan, 0, ',', '.') }}', false)" 
                                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1">
                                            <i class="fa-solid fa-file-invoice-dollar"></i> Input Bukti Transfer
                                        </button>
                                    @endif
                                @else
                                    {{-- Tombol Bayar Sisa Payment Gateway DOKU --}}
                                    <a href="{{ route('checkout.pay', $order->order_number) }}" class="px-3.5 py-2 bg-[#3e2723] hover:bg-[#2c1b18] text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1">
                                        <i class="fa-solid fa-credit-card"></i> Bayar Sisa
                                    </a>
                                @endif
                            @endif

                            {{-- TOMBOL BATALKAN PESANAN --}}
                            @if(!in_array($statusVal, ['completed', 'cancelled']))
                                <button type="button" 
                                        onclick="openCancelModal('{{ $order->order_number }}', '{{ addslashes($order->customer_name) }}', '{{ $order->customer_phone }}', '{{ number_format($order->total_amount, 0, ',', '.') }}', '{{ number_format($order->amount_paid, 0, ',', '.') }}', '{{ addslashes($itemNames) }}')" 
                                        class="px-3.5 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 shadow-sm transition flex items-center gap-1">
                                    <i class="fa-solid fa-ban"></i> Batalkan
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- MODAL DETAIL PER PESANAN --}}
                <div id="modal-order-{{ $order->id }}" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
                    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 md:p-8 rounded-[2.5rem] bg-white/80 backdrop-blur-3xl border border-white/80 shadow-2xl relative space-y-5 my-auto">
                        <div class="flex justify-between items-center pb-3 border-b border-[#3e2723]/15">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-gray-400">Rincian Pre-Order</span>
                                <h3 class="text-lg font-black text-[#3e2723]">{{ $order->order_number }}</h3>
                            </div>
                            <button type="button" onclick="closeOrderDetail('{{ $order->id }}')" class="w-8 h-8 rounded-full bg-white flex items-center justify-center font-bold text-[#3e2723] shadow-md hover:bg-gray-100 transition">✕</button>
                        </div>

                        {{-- TIMELINE PROGRESS DAPUR --}}
                        @php
                            $steps = ['pending' => 1, 'confirmed' => 2, 'preparing' => 3, 'ready' => 4, 'completed' => 5];
                            $currentStep = $steps[$statusVal] ?? 1;
                        @endphp
                        <div class="py-2 bg-white/50 p-4 rounded-2xl border border-white/60">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#3e2723]/70 mb-3 text-center">Status Pengerjaan Dapur</p>
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ $currentStep >= 1 ? 'bg-[#3e2723] text-white' : 'bg-gray-200 text-gray-400' }}"><i class="fa-solid fa-check"></i></div>
                                    <p class="text-[10px] font-bold {{ $currentStep >= 1 ? 'text-[#3e2723]' : 'text-gray-400' }}">Diterima</p>
                                </div>
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ $currentStep >= 2 ? 'bg-[#3e2723] text-white' : 'bg-gray-200 text-gray-400' }}"><i class="fa-solid fa-thumbs-up"></i></div>
                                    <p class="text-[10px] font-bold {{ $currentStep >= 2 ? 'text-[#3e2723]' : 'text-gray-400' }}">Konfirmasi</p>
                                </div>
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ $currentStep >= 3 ? 'bg-[#3e2723] text-white' : 'bg-gray-200 text-gray-400' }}"><i class="fa-solid fa-fire-burner"></i></div>
                                    <p class="text-[10px] font-bold {{ $currentStep >= 3 ? 'text-[#3e2723]' : 'text-gray-400' }}">Dipanggang</p>
                                </div>
                                <div class="space-y-1">
                                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-xs {{ $currentStep >= 4 ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-400' }}"><i class="fa-solid {{ $orderTypeVal === 'pickup' ? 'fa-bag-shopping' : 'fa-truck-fast' }}"></i></div>
                                    <p class="text-[10px] font-bold {{ $currentStep >= 4 ? 'text-emerald-800' : 'text-gray-400' }}">Siap</p>
                                </div>
                            </div>
                        </div>

                        {{-- DAFTAR ITEM KUE --}}
                        <div class="space-y-2">
                            <p class="text-[10px] font-bold uppercase text-gray-400">Detail Menu Dipesan</p>
                            <div class="divide-y divide-gray-200 bg-white/60 rounded-2xl p-4 border border-white">
                                @foreach($order->items as $item)
                                    <div class="py-2 flex justify-between items-center text-xs">
                                        <div>
                                            <p class="font-bold text-[#3e2723]">{{ $item->product_name }}</p>
                                            <p class="text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                        </div>
                                        <span class="font-bold text-[#3e2723]">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- INFORMASI ALAMAT --}}
                        @if($orderTypeVal === 'delivery')
                            <div class="p-3 bg-white/60 rounded-xl border border-white text-xs space-y-1">
                                <p class="text-[10px] font-bold uppercase text-gray-400">Alamat Pengiriman</p>
                                <p class="font-semibold text-[#3e2723]">{{ $order->delivery_address ?? 'Belum diisi' }}</p>
                            </div>
                        @endif

                        {{-- TAMPILAN FILE BUKTI TRANSFER JIKA SUDAH ADA --}}
                        @if($hasProofFile)
                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs space-y-1">
                                <p class="text-[10px] font-bold uppercase text-emerald-800">Bukti Transfer Terunggah</p>
                                <button type="button" 
                                        onclick="openViewProofModal('{{ $proofUrl }}', '{{ $order->order_number }}', {{ json_encode($historyFormatted) }})" 
                                        class="text-emerald-700 font-bold underline flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-image"></i> Lihat Foto Bukti Pembayaran Saya
                                </button>
                            </div>
                        @endif

                        {{-- RINGKASAN TAGIHAN & PELUNASAN --}}
                        <div class="p-4 rounded-2xl bg-[#3e2723] text-white space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span>Total Biaya</span>
                                <span class="font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Sudah Dibayar</span>
                                <span class="font-bold text-emerald-400">Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-white/20 pt-2 flex justify-between text-sm font-black">
                                <span>Sisa Tagihan Pelunasan</span>
                                <span class="{{ $sisaTagihan > 0 ? 'text-amber-300' : 'text-emerald-300' }}">
                                    Rp {{ number_format($sisaTagihan > 0 ? $sisaTagihan : 0, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- JIKA SISA DP (PARTIAL), TAMPILKAN INSTRUKSI PELUNASAN KASIR/OFFLINE --}}
                            @if($isDpAndPartial)
                                <div class="pt-2 border-t border-white/10 text-center">
                                    <p class="text-[11px] text-amber-200 font-medium">
                                        <i class="fa-solid fa-hand-holding-dollar mr-1"></i>
                                        Mohon lakukan pelunasan sebesar <strong class="text-amber-300 font-bold">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong> saat {{ $orderTypeVal === 'pickup' ? 'pengambilan kue di toko' : 'pengiriman kue oleh kurir' }}.
                                    </p>
                                </div>
                            @elseif($canPayOrUpload)
                                <div class="pt-2">
                                    @if($paymentMethodVal === 'manual_wa')
                                        @if($isLimitReached)
                                            <button type="button" disabled class="block w-full py-3 bg-gray-500 text-white font-bold text-center rounded-xl cursor-not-allowed">
                                                <i class="fa-solid fa-lock mr-1"></i> Batas Maksimal 5x Upload Tercapai
                                            </button>
                                        @else
                                            <button type="button" 
                                                    onclick="openUploadProofModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ number_format($sisaTagihan, 0, ',', '.') }}', {{ $hasProofFile ? 'true' : 'false' }})" 
                                                    class="block w-full py-3 {{ $hasProofFile ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white font-bold text-center rounded-xl transition shadow-md">
                                                <i class="fa-solid {{ $hasProofFile ? 'fa-arrows-rotate' : 'fa-file-invoice-dollar' }} mr-1"></i> 
                                                {{ $hasProofFile ? 'Ganti Foto Bukti Transfer (' . $uploadCount . '/5)' : 'Upload Bukti Transfer Sisa' }}
                                            </button>
                                        @endif
                                    @else
                                        <a href="{{ route('checkout.pay', $order->order_number) }}" class="block w-full py-3 bg-[#c8a97e] hover:bg-[#b8860b] text-white font-bold text-center rounded-xl transition shadow-md">
                                            <i class="fa-solid fa-credit-card mr-1"></i> Bayar Sisa Pelunasan Sekarang
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(request('search') && request('search') !== 'search')
        {{-- TAMPILAN JIKA TIDAK DITEMUKAN --}}
        <div class="glass border border-white/60 rounded-[2.5rem] p-10 shadow-xl text-center space-y-3">
            <div class="w-16 h-16 rounded-full bg-rose-500/10 text-rose-600 flex items-center justify-center text-2xl mx-auto">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h3 class="text-base font-bold text-[#3e2723]">Pesanan Tidak Ditemukan</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto">Tidak ditemukan pesanan yang cocok persis dengan kata kunci "<span class="font-bold text-[#3e2723]">{{ request('search') }}</span>". Pastikan nomor telepon ditulis lengkap dan sesuai saat checkout.</p>
        </div>
    @else
        {{-- STATE KOSONG DEFAULT --}}
        <div class="glass border border-white/60 rounded-[2.5rem] p-12 shadow-xl text-center space-y-3">
            <div class="w-16 h-16 rounded-full bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-2xl mx-auto">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 class="text-base font-bold text-[#3e2723]">Riwayat Pesanan Terproteksi</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto">Silakan ketik nomor telepon Anda pada kolom pencarian di atas untuk memverifikasi dan menampilkan seluruh daftar Pre-Order Anda.</p>
        </div>
    @endif

</main>

{{-- MODAL UNGGAH / GANTI BUKTI TRANSFER MANUAL --}}
<div id="uploadProofModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2.5rem] shadow-2xl p-6 md:p-8 space-y-5 text-center my-auto">
        <div id="proofModalIcon" class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mx-auto shadow-inner">
            <i class="fa-solid fa-file-arrow-up"></i>
        </div>

        <div>
            <h3 id="proofModalTitle" class="text-lg font-black text-[#3e2723]">Upload Bukti Transfer</h3>
            <p class="text-xs text-gray-500 mt-1">No. Order: <span id="proofModalOrderNo" class="font-bold text-[#3e2723]"></span></p>
            <p class="text-xs font-bold text-rose-600 mt-0.5">Sisa Tagihan: Rp <span id="proofModalSisa"></span></p>
        </div>

        {{-- FORM INPUT FILE --}}
        <form id="uploadProofForm" action="{{ route('orders.uploadProof') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="order_id" id="proofOrderIdInput">

            <div class="text-left space-y-1">
                <label class="block text-xs font-bold text-gray-600 uppercase">Pilih Foto Bukti Transfer <span class="text-red-500">*</span></label>
                <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/jpg,image/webp" required class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#3e2723] file:text-white hover:file:bg-[#2c1b18] border border-gray-200 rounded-xl bg-white p-1">
                <p class="text-[10px] text-gray-400">Format: JPG, JPEG, PNG, WebP (Maksimal 5MB)</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeUploadProofModal()" class="w-1/2 py-3 text-xs font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-2xl transition">
                    Batal
                </button>
                <button type="submit" id="proofSubmitBtn" class="w-1/2 py-3 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-2xl shadow-lg transition">
                    Simpan Bukti
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 🖼️ MODAL POPUP LIHAT FOTO BUKTI TRANSFER PENGGUNA --}}
<div id="viewProofModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2.5rem] shadow-2xl p-6 md:p-8 space-y-4 text-center my-auto max-h-[90vh] overflow-y-auto">
        
        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
            <div class="flex items-center gap-2 text-[#3e2723]">
                <i class="fa-solid fa-receipt text-lg text-emerald-600"></i>
                <h3 class="font-black text-sm">Bukti Transfer Saya - <span id="viewProofOrderNo"></span></h3>
            </div>
            <button type="button" onclick="closeViewProofModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- WADAH FOTO BUKTI UTAMA --}}
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-left text-gray-500 mb-1">Foto Bukti Transfer</p>
            <div class="relative bg-gray-100 rounded-2xl overflow-hidden border border-gray-200 max-h-[45vh] flex items-center justify-center">
                <img id="viewProofImg" src="" alt="Foto Bukti Transfer Saya" class="max-h-[43vh] w-auto object-contain rounded-xl shadow">
            </div>
        </div>

        {{-- WADAH RIWAYAT UNGGAHAN SEBELUMNYA (JIKA ADA) --}}
        <div id="customerProofHistoryContainer" class="hidden pt-3 border-t border-gray-200 text-left space-y-2">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-600 flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left text-amber-700"></i>
                Riwayat Unggahan Saya
            </p>

            <div id="customerProofHistoryGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                {{-- Diisi secara dinamis lewat JavaScript --}}
            </div>
        </div>

        <div class="pt-2 flex justify-between items-center gap-2">
            <a id="viewProofExternalLink" href="#" target="_blank" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 transition flex items-center gap-1.5">
                <i class="fa-solid fa-up-right-from-square"></i> Tab Baru
            </a>
            <button type="button" onclick="closeViewProofModal()" class="px-5 py-2 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow hover:bg-[#2c1b18] transition">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI BATAL PESANAN --}}
<div id="cancelOrderModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2.5rem] shadow-2xl p-6 md:p-8 space-y-5 text-center my-auto">
        <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <div>
            <h3 class="text-lg font-black text-[#3e2723]">Pengajuan Pembatalan Pesanan</h3>
            <p class="text-xs text-gray-500 mt-1">Nomor Pesanan: <span id="cancelModalOrderNo" class="font-bold text-[#3e2723]"></span></p>
        </div>

        <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 text-left text-xs text-amber-900 space-y-2 leading-relaxed">
            <p class="font-bold border-b border-amber-200/60 pb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-info text-amber-600"></i> Ketentuan Refund & Pembatalan:
            </p>
            <ul class="list-disc pl-4 space-y-1 text-[11px]">
                <li>Pengajuan pembatalan akan terhubung secara langsung ke WhatsApp Admin Edelweiss Bakery.</li>
                <li><strong>Pengembalian Dana (Refund):</strong> Hanya dapat diproses apabila bahan baku pesanan Anda <u>belum dibeli/diproduksi</u>.</li>
                <li>Jika pesanan sudah memasuki tahap belanja bahan atau produksi, pembayaran <u>tidak dapat di-refund</u>.</li>
            </ul>
        </div>

        <div class="text-left pt-1">
            <label class="flex items-start gap-2.5 cursor-pointer text-xs text-gray-700 select-none">
                <input type="checkbox" id="cancelTermsCheckbox" onchange="toggleCancelButton()" class="mt-0.5 w-4 h-4 accent-[#3e2723] rounded">
                <span class="text-[11px] text-gray-600">
                    Saya memahami dan menyetujui seluruh ketentuan refund & pembatalan Pre-Order di atas.
                </span>
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="button" onclick="closeCancelModal()" class="w-1/2 py-3 text-xs font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-2xl transition">
                Batal
            </button>
            <button type="button" id="confirmCancelBtn" disabled onclick="submitCancelToWhatsApp()" class="w-1/2 py-3 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 disabled:bg-gray-300 disabled:cursor-not-allowed rounded-2xl shadow-lg transition flex items-center justify-center gap-1.5">
                <i class="fa-brands fa-whatsapp text-sm"></i> Ajukan ke WA
            </button>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
    const ADMIN_WA = '6287794082895';
    let selectedCancelData = {};

    function showOrderDetail(id) {
        document.getElementById('modal-order-' + id).classList.remove('hidden');
    }

    function closeOrderDetail(id) {
        document.getElementById('modal-order-' + id).classList.add('hidden');
    }

    // LOGIKA MODAL UNGGAH / GANTI BUKTI TRANSFER
    function openUploadProofModal(orderId, orderNo, sisaTagihan, isChange = false) {
        document.getElementById('proofOrderIdInput').value = orderId;
        document.getElementById('proofModalOrderNo').innerText = orderNo;
        document.getElementById('proofModalSisa').innerText = sisaTagihan;

        const title = document.getElementById('proofModalTitle');
        const icon = document.getElementById('proofModalIcon');
        const submitBtn = document.getElementById('proofSubmitBtn');

        if(isChange) {
            title.innerText = "Ganti Foto Bukti Transfer";
            icon.className = "w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto shadow-inner";
            submitBtn.className = "w-1/2 py-3 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-2xl shadow-lg transition";
            submitBtn.innerText = "Perbarui Bukti";
        } else {
            title.innerText = "Upload Bukti Transfer";
            icon.className = "w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mx-auto shadow-inner";
            submitBtn.className = "w-1/2 py-3 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-2xl shadow-lg transition";
            submitBtn.innerText = "Simpan Bukti";
        }

        document.getElementById('uploadProofModal').classList.remove('hidden');
    }

    function closeUploadProofModal() {
        document.getElementById('uploadProofModal').classList.add('hidden');
    }

    // LOGIKA MODAL PRATINJAU BUKTI TRANSFER BESERTA RIWAYAT
    function openViewProofModal(imageUrl, orderNo, historyList = []) {
        document.getElementById('viewProofImg').src = imageUrl;
        document.getElementById('viewProofExternalLink').href = imageUrl;
        document.getElementById('viewProofOrderNo').innerText = orderNo;

        const historyContainer = document.getElementById('customerProofHistoryContainer');
        const historyGrid = document.getElementById('customerProofHistoryGrid');
        historyGrid.innerHTML = '';

        if (Array.isArray(historyList) && historyList.length > 0) {
            historyContainer.classList.remove('hidden');
            historyList.forEach((item, index) => {
                const seq = item.sequence || (index + 1);
                const html = `
                    <div class="p-2 bg-gray-50 border border-gray-200 rounded-xl space-y-1 text-center cursor-pointer hover:border-emerald-500 transition shadow-sm" onclick="switchCustomerProof('${item.url}')">
                        <div class="h-24 bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                            <img src="${item.url}" class="h-full w-auto object-cover rounded" alt="Riwayat Bukti">
                        </div>
                        <p class="text-[10px] font-bold text-gray-700 truncate">Upload #${seq}</p>
                        <p class="text-[9px] text-gray-400">${item.uploaded_at || ''}</p>
                    </div>
                `;
                historyGrid.insertAdjacentHTML('beforeend', html);
            });
        } else {
            historyContainer.classList.add('hidden');
        }

        document.getElementById('viewProofModal').classList.remove('hidden');
    }

    function switchCustomerProof(url) {
        document.getElementById('viewProofImg').src = url;
        document.getElementById('viewProofExternalLink').href = url;
    }

    function closeViewProofModal() {
        document.getElementById('viewProofModal').classList.add('hidden');
    }

    // LOGIKA MODAL BATALKAN PESANAN
    function openCancelModal(orderNo, custName, custPhone, totalAmt, paidAmt, items) {
        selectedCancelData = { orderNo, custName, custPhone, totalAmt, paidAmt, items };
        document.getElementById('cancelModalOrderNo').innerText = orderNo;
        document.getElementById('cancelTermsCheckbox').checked = false;
        document.getElementById('confirmCancelBtn').disabled = true;
        document.getElementById('cancelOrderModal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelOrderModal').classList.add('hidden');
    }

    function toggleCancelButton() {
        const checkbox = document.getElementById('cancelTermsCheckbox');
        document.getElementById('confirmCancelBtn').disabled = !checkbox.checked;
    }

    function submitCancelToWhatsApp() {
        if (!selectedCancelData.orderNo) return;

        let waMessage = `Halo Admin Edelweiss Bakery,\n`;
        waMessage += `Saya ingin mengajukan PEMBATALAN & PENGEMBALIAN DANA (REFUND) untuk pesanan berikut:\n\n`;
        waMessage += `DETAIL PESANAN:\n`;
        waMessage += `- No. Order: *${selectedCancelData.orderNo}*\n`;
        waMessage += `- Nama Pemesan: *${selectedCancelData.custName}*\n`;
        waMessage += `- No. WA Pemesan: ${selectedCancelData.custPhone}\n`;
        waMessage += `- Item Pesanan: ${selectedCancelData.items}\n`;
        waMessage += `- Total Biaya: Rp ${selectedCancelData.totalAmt}\n`;
        waMessage += `- Jumlah Sudah Dibayar: Rp ${selectedCancelData.paidAmt}\n\n`;
        waMessage += `KETERANGAN:\nSaya memahami ketentuan bahwa refund hanya dapat diproses apabila bahan baku belum diproduksi.\n\nMohon bantuan Admin untuk mengecek pesanan saya. Terima kasih!`;

        window.open(`https://wa.me/${ADMIN_WA}?text=${encodeURIComponent(waMessage)}`, '_blank');
        closeCancelModal();
    }
</script>

</body>
</html>