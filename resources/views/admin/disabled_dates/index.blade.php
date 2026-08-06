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

    {{-- ALERT ERROR FLASHDATA --}}
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 text-xs font-bold flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button @click="show = false" class="text-rose-700 hover:text-rose-950">
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

    {{-- ========================================================================= --}}
    {{-- KALENDER VISUAL TANGGAL TERKUNCI --}}
    {{-- ========================================================================= --}}
    @php
        $calMonth = request('cal_month', date('n'));
        $calYear = request('cal_year', date('Y'));

        $firstDayOfMonth = \Carbon\Carbon::createFromDate($calYear, $calMonth, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $startDayOfWeek = $firstDayOfMonth->dayOfWeek;

        $lockedMap = collect($allDisabledDates ?? $disabledDates->items())->keyBy(function($item) {
            return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
        });

        $prevMonthDate = $firstDayOfMonth->copy()->subMonth();
        $nextMonthDate = $firstDayOfMonth->copy()->addMonth();
    @endphp

    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-b border-[#3e2723]/10 pb-4">
            <h4 class="text-sm font-bold text-[#3e2723] flex items-center gap-2">
                <i class="fa-regular fa-calendar-days text-rose-600"></i> Visualisasi Kalender Libur / Lock
            </h4>

            {{-- Navigasi Bulan & Tahun --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.disabled_dates.index', array_merge(request()->query(), ['cal_month' => $prevMonthDate->month, 'cal_year' => $prevMonthDate->year])) }}" 
                   class="w-8 h-8 rounded-xl bg-white/60 border border-white/50 hover:bg-white flex items-center justify-center text-[#3e2723] transition shadow-sm"
                   title="Bulan Sebelumnya">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>

                <span class="text-xs font-black text-[#3e2723] uppercase tracking-wider min-w-[120px] text-center">
                    {{ $firstDayOfMonth->translatedFormat('F Y') }}
                </span>

                <a href="{{ route('admin.disabled_dates.index', array_merge(request()->query(), ['cal_month' => $nextMonthDate->month, 'cal_year' => $nextMonthDate->year])) }}" 
                   class="w-8 h-8 rounded-xl bg-white/60 border border-white/50 hover:bg-white flex items-center justify-center text-[#3e2723] transition shadow-sm"
                   title="Bulan Selanjutnya">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        {{-- Legend Indicator --}}
        <div class="flex items-center gap-4 text-[11px] font-bold text-gray-600 pt-1">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-md bg-rose-500 shadow-sm inline-block"></span>
                <span>Terkunci / Libur (Merah)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-md bg-white/80 border border-gray-300 inline-block"></span>
                <span>Buka / Normal</span>
            </div>
        </div>

        {{-- Grid Kalender --}}
        <div class="grid grid-cols-7 gap-1.5 text-center">
            @foreach(['Ming', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
            <div class="py-2 text-[10px] font-black uppercase text-[#3e2723]/60 tracking-wider">
                {{ $dayName }}
            </div>
            @endforeach

            @for ($i = 0; $i < $startDayOfWeek; $i++)
            <div class="h-14 sm:h-16 rounded-2xl bg-gray-100/30 border border-transparent"></div>
            @endfor

            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDateString = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $day);
                    $isLocked = $lockedMap->has($currentDateString);
                    $lockInfo = $isLocked ? $lockedMap->get($currentDateString) : null;
                    $isToday = $currentDateString === date('Y-m-d');
                @endphp

                <div class="relative group h-14 sm:h-16 p-1.5 rounded-2xl border transition flex flex-col justify-between
                    {{ $isLocked ? 'bg-rose-500 text-white border-rose-600 shadow-md' : 'bg-white/60 border-white/50 text-[#3e2723] hover:bg-white/90' }}
                    {{ $isToday && !$isLocked ? 'ring-2 ring-[#c8a97e] font-black' : '' }}">
                    
                    <div class="flex justify-between items-center w-full">
                        <span class="text-xs font-black">{{ $day }}</span>
                        @if($isLocked)
                            <i class="fa-solid fa-lock text-[9px] text-white/80"></i>
                        @elseif($isToday)
                            <span class="text-[8px] px-1 bg-[#c8a97e] text-white font-bold rounded">Hari Ini</span>
                        @endif
                    </div>

                    @if($isLocked)
                        <p class="text-[9px] font-bold truncate text-left text-white/90 px-0.5" title="{{ $lockInfo->reason ?? 'Kuota Penuh / Toko Libur' }}">
                            {{ $lockInfo->reason ?? 'Kuota Penuh' }}
                        </p>

                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-30 w-40 p-2 bg-[#3e2723] text-white text-[10px] rounded-xl shadow-2xl pointer-events-none text-left space-y-0.5">
                            <p class="font-bold text-amber-300">{{ \Carbon\Carbon::parse($currentDateString)->translatedFormat('d F Y') }}</p>
                            <p class="text-gray-200">Keterangan: {{ $lockInfo->reason ?? 'Kuota Penuh / Toko Libur' }}</p>
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- MAIN CONTENT GRID (FORM INPUT + TABEL DATA) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI: FORM KUNCI TANGGAL BARU (SINGLE & RENTANG TANGGAL) --}}
        <div class="lg:col-span-1" x-data="{ mode: 'single', startDate: '{{ date('Y-m-d') }}' }">
            <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4 sticky top-0">
                <div class="flex items-center justify-between border-b border-[#3e2723]/10 pb-3">
                    <h4 class="text-sm font-bold text-[#3e2723] flex items-center gap-2">
                        <i class="fa-solid fa-lock text-[#c8a97e]"></i> Kunci Tanggal Baru
                    </h4>
                </div>

                {{-- SWITCH MODE: TUNGGAL VS RENTANG TANGGAL --}}
                <div class="p-1 bg-white/60 border border-white/50 rounded-xl flex gap-1 text-[11px] font-bold text-[#3e2723]">
                    <button type="button" 
                            @click="mode = 'single'" 
                            :class="mode === 'single' ? 'bg-[#3e2723] text-white shadow' : 'hover:bg-white/50 text-gray-600'"
                            class="flex-1 py-1.5 rounded-lg transition text-center flex items-center justify-center gap-1">
                        <i class="fa-regular fa-calendar-check"></i> 1 Hari
                    </button>
                    <button type="button" 
                            @click="mode = 'range'" 
                            :class="mode === 'range' ? 'bg-[#3e2723] text-white shadow' : 'hover:bg-white/50 text-gray-600'"
                            class="flex-1 py-1.5 rounded-lg transition text-center flex items-center justify-center gap-1">
                        <i class="fa-solid fa-calendar-week"></i> Rentang Tanggal
                    </button>
                </div>

                <form action="{{ route('admin.disabled_dates.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="input_mode" :value="mode">

                    {{-- MODE 1: SINGLE TANGGAL (SEMULA) --}}
                    <div x-show="mode === 'single'" x-transition>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-600">
                            Pilih Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="date" 
                               value="{{ old('date', date('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               :required="mode === 'single'"
                               class="w-full px-4 py-3 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-xs font-bold text-[#3e2723] transition @error('date') border-red-400 @enderror">
                        @error('date')
                            <p class="text-[11px] text-red-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- MODE 2: RENTANG TANGGAL (NEW FEATURE) --}}
                    <div x-show="mode === 'range'" x-transition class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5 text-gray-600">
                                Dari Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   x-model="startDate"
                                   min="{{ date('Y-m-d') }}"
                                   :required="mode === 'range'"
                                   class="w-full px-4 py-2.5 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-xs font-bold text-[#3e2723] transition @error('start_date') border-red-400 @enderror">
                            @error('start_date')
                                <p class="text-[11px] text-red-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5 text-gray-600">
                                Sampai Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="end_date" 
                                   value="{{ old('end_date', date('Y-m-d')) }}"
                                   :min="startDate"
                                   :required="mode === 'range'"
                                   class="w-full px-4 py-2.5 rounded-xl border border-white/60 bg-white/60 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-xs font-bold text-[#3e2723] transition @error('end_date') border-red-400 @enderror">
                            @error('end_date')
                                <p class="text-[11px] text-red-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <p class="text-[10px] text-amber-800 bg-amber-50/80 p-2 rounded-lg border border-amber-200">
                            <i class="fa-solid fa-lightbulb text-amber-600"></i> Seluruh tanggal dalam rentang ini akan dikunci sekaligus.
                        </p>
                    </div>

                    {{-- ALASAN / KETERANGAN --}}
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
                        <i class="fa-solid fa-lock"></i> 
                        <span x-text="mode === 'range' ? 'Kunci Rentang Tanggal' : 'Kunci Tanggal'"></span>
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