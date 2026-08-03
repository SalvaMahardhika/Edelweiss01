@extends('admin_layouts.master')

@section('page_title', 'Manajemen Lock Tanggal')

@section('content')
<div x-data="{ 
    // State Modal Delete / Open Lock
    showDeleteModal: false,
    deleteFormAction: '',
    deleteFormattedDate: '',

    openDeleteModal(actionUrl, formattedDate) {
        this.deleteFormAction = actionUrl;
        this.deleteFormattedDate = formattedDate;
        this.showDeleteModal = true;
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

    {{-- HEADER KETERANGAN SINGKAT --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-black text-[#3e2723] flex items-center gap-2">
                <i class="fa-solid fa-calendar-xmark text-rose-600"></i> Penguncian Tanggal (Block Out Dates)
            </h3>
            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                Tanggal yang dikunci di sini otomatis <strong>tidak dapat dipilih oleh pelanggan</strong> pada halaman checkout.
                Tanggal yang telah lewat hari (*real-time*) akan dibersihkan secara otomatis oleh sistem.
            </p>
        </div>
        <div class="px-4 py-2 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-amber-900 text-xs font-bold shrink-0 flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-amber-600"></i>
            <span>{{ $disabledDates->total() }} Tanggal Terkunci</span>
        </div>
    </div>

    {{-- MAIN CONTENT GRID (FORM INPUT + TABEL DATA) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI: FORM KUNCI TANGGAL BARU --}}
        <div class="lg:col-span-1">
            <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4 sticky top-0">
                <h4 class="text-sm font-bold text-[#3e2723] border-b border-[#3e2723]/10 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-[#c8a97e]"></i> Kunci Tanggal Baru
                </h4>

                <form action="{{ route('admin.disabled_dates.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">
                            Pilih Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="date" 
                               value="{{ old('date', date('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               required 
                               class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-xs font-bold text-[#3e2723] transition @error('date') border-red-400 @enderror">
                        @error('date')
                            <p class="text-[11px] text-red-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">
                            Alasan / Keterangan (Opsional)
                        </label>
                        <input type="text" 
                               name="reason" 
                               value="{{ old('reason') }}"
                               placeholder="Contoh: Kuota Penuh, Libur Toko, dll..." 
                               class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-xs font-medium text-[#3e2723] transition @error('reason') border-red-400 @enderror">
                        <p class="text-[10px] text-gray-500 mt-1">Jika dikosongkan, default: "Kuota Penuh / Toko Libur".</p>
                        @error('reason')
                            <p class="text-[11px] text-red-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow-lg hover:bg-[#2c1b18] transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-lock"></i> Kunci Tanggal
                    </button>
                </form>
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL DAFTAR TANGGAL TERKUNCI --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 space-y-4">
                
                {{-- BAR PENCARIAN --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <form method="GET" action="{{ route('admin.disabled_dates.index') }}" class="flex gap-2 w-full">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tanggal (YYYY-MM-DD) atau alasan..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
                        </div>
                        <button type="submit" class="px-4 py-2.5 bg-[#3e2723] text-white text-xs font-bold rounded-xl shadow hover:bg-[#2c1b18] transition">
                            Cari
                        </button>
                        @if(request('search'))
                        <a href="{{ route('admin.disabled_dates.index') }}" class="px-3 py-2.5 bg-white/60 text-[#3e2723] text-xs font-bold rounded-xl border border-white/50 hover:bg-white transition flex items-center justify-center">
                            Reset
                        </a>
                        @endif
                    </form>
                </div>

                {{-- TABEL DATA --}}
                <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                                <th class="px-4 py-3.5">Tanggal Terkunci</th>
                                <th class="px-4 py-3.5">Hari</th>
                                <th class="px-4 py-3.5">Alasan / Status</th>
                                <th class="px-4 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/30 text-xs font-medium">
                            @forelse($disabledDates as $item)
                            @php
                                $carbonDate = \Carbon\Carbon::parse($item->date);
                            @endphp
                            <tr class="hover:bg-white/30 transition">
                                {{-- TANGGAL --}}
                                <td class="px-4 py-3.5 font-black text-[#3e2723]">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar text-rose-600"></i>
                                        <span>{{ $carbonDate->translatedFormat('d F Y') }}</span>
                                    </div>
                                </td>

                                {{-- HARI --}}
                                <td class="px-4 py-3.5 text-gray-600 font-bold">
                                    {{ $carbonDate->translatedFormat('l') }}
                                </td>

                                {{-- ALASAN --}}
                                <td class="px-4 py-3.5">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200/60 inline-block">
                                        {{ $item->reason }}
                                    </span>
                                </td>

                                {{-- AKSI BUKA KUNCI --}}
                                <td class="px-4 py-3.5 text-center">
                                    <button type="button" 
                                            @click="openDeleteModal('{{ route('admin.disabled_dates.destroy', $item->id) }}', '{{ $carbonDate->translatedFormat('d F Y') }}')"
                                            class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl shadow hover:bg-emerald-700 transition text-[11px] font-bold inline-flex items-center gap-1"
                                            title="Buka Kunci Tanggal Ini">
                                        <i class="fa-solid fa-lock-open text-xs"></i> Buka Kunci
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">
                                    Belum ada tanggal yang dikunci. Semua tanggal beroperasi normal.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-4">
                    {{ $disabledDates->links() }}
                </div>

            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL KONFIRMASI BUKA KUNCI TANGGAL --}}
    {{-- ========================================================================= --}}
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.away="showDeleteModal = false" class="bg-white/95 backdrop-blur-2xl border border-white/80 rounded-[2rem] p-6 max-w-md w-full shadow-2xl text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fa-solid fa-lock-open"></i>
            </div>

            <div>
                <h3 class="font-black text-lg text-[#3e2723]">Buka Kunci Tanggal?</h3>
                <p class="text-xs text-gray-600 mt-1">
                    Apakah Anda yakin ingin membuka penguncian tanggal <strong class="text-[#3e2723]" x-text="deleteFormattedDate"></strong>? 
                    Pelanggan akan dapat memilih kembali tanggal ini di halaman checkout.
                </p>
            </div>

            <form :action="deleteFormAction" method="POST" class="pt-2 flex gap-3 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 bg-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-300 transition w-1/2">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition w-1/2">
                    Ya, Buka Kunci
                </button>
            </form>
        </div>
    </div>

</div>
@endsection