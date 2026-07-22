@extends('admin_layouts.master')

@section('page_title', 'Manajemen Galeri Portofolio')

@section('content')
{{-- 💎 AREA SCROLL MANDIRI PANEL UTAMA CMS --}}
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- HEADER MANAGEMENT INTERFACE --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#3e2723]">Daftar Album Galeri</h3>
            <p class="text-sm text-gray-500 mt-0.5">Kelola portofolio kue kustom, dokumentasi dapur, dan momen pameran Edelweiss Bakery.</p>
        </div>
        <button onclick="openAddModal()" class="px-5 py-2.5 bg-[#3e2723] text-white font-semibold rounded-xl text-sm shadow-md hover:bg-[#2c1b18] transition duration-300 inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Buat Album Baru
        </button>
    </div>

    {{-- TABLE DATA PUSAT GALERI CMS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4 w-24 text-center">Sampul</th>
                        <th class="px-6 py-4">Judul Album</th>
                        <th class="px-6 py-4">Deskripsi Cerita</th>
                        <th class="px-6 py-4 text-center">Jumlah Foto</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    @foreach($galeri as $album)
                    @php
                        $folder = public_path('img/galeri/' . $album->album);
                        $files = file_exists($folder) ? array_values(array_diff(scandir($folder), ['.', '..'])) : [];
                    @endphp
                    <tr class="hover:bg-white/30 transition">
                        <td class="px-6 py-3 text-center">
                            <img src="{{ count($files) > 0 ? asset('img/galeri/' . $album->album . '/' . $files[0]) : asset('img/logo/logo2.png') }}" 
                                 class="w-12 h-12 object-cover rounded-xl border border-white bg-gray-100 mx-auto shadow-sm">
                        </td>
                        <td class="px-6 py-3">
                            <p class="font-bold text-[#2d1f1b]">{{ $album->judul }}</p>
                            <p class="text-[11px] text-amber-800 font-mono mt-0.5">dir: {{ $album->album }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-xs text-gray-500 line-clamp-2 max-w-sm font-normal">{{ $album->deskripsi }}</p>
                        </td>
                        <td class="px-6 py-3 text-center text-[#3e2723] font-bold">
                            {{ count($files) }} Foto
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('galeri.edit', $album->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Kelola Isi Album">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <button type="button" onclick="triggerDelete('{{ route('galeri.destroy', $album->id) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Hapus Album">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🔒 COMPONENT: MODAL POPUP INPUT ALBUM BARU --}}
<div id="addAlbumModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-lg p-6 rounded-[2rem] bg-white/40 backdrop-blur-3xl border border-white/50 shadow-2xl relative space-y-4 my-auto">
        <div class="flex justify-between items-center pb-2 border-b border-[#3e2723]/15">
            <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-regular fa-images mr-2"></i> Form Pembuatan Album Baru</h3>
            <button type="button" onclick="closeAddModal()" class="w-8 h-8 rounded-full bg-white/40 flex items-center justify-center font-bold text-[#3e2723] hover:bg-white/70">✕</button>
        </div>

        <form id="addAlbumForm" method="POST" action="{{ route('galeri.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Judul Portofolio Album</label>
                <input type="text" id="draft_judul" name="judul" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Deskripsi / Cerita Album Kue</label>
                <textarea id="draft_deskripsi" name="deskripsi" rows="4" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition"></textarea>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Unggah Berkas Gambar (Bisa Banyak Sekaligus)</label>
                <input type="file" name="gambar[]" multiple required class="w-full mt-1 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#3e2723] file:text-white file:cursor-pointer">
                <p class="text-[10px] text-gray-400 mt-1">*Sistem otomatis mengonversi gambar ke WebP dan merampingkan resolusi.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeAddModal()" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-white/60 border border-white/40 text-[#3e2723] hover:bg-white/80 transition">Batal</button>
                <button type="submit" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-[#3e2723] text-white shadow-md hover:bg-[#2c1b18] transition">Terbitkan Album</button>
            </div>
        </form>
    </div>
</div>

{{-- 🚨 CUSTOM GLOBAL SYSTEM MODAL ALERT --}}
<div id="systemAlertModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl text-center space-y-4">
        <div id="alertIconContainer" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md">
            <i id="alertIcon" class="fa-solid"></i>
        </div>
        <div>
            <h4 id="alertTitle" class="text-lg font-bold text-[#3e2723]">Notifikasi</h4>
            <p id="alertDescription" class="text-sm text-gray-600 mt-1 leading-relaxed"></p>
        </div>
        <div id="alertActionArea" class="flex gap-2 pt-2">
            <button onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition">Oke</button>
        </div>
    </div>
</div>

{{-- Hidden Form Action Delete --}}
<form id="globalDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // --- CONTROL MODAL TAMBAH ---
    function openAddModal() {
        document.getElementById('addAlbumModal').classList.remove('hidden');
    }
     Kak // Tutup Modal Sekaligus Bersihkan Draft di localstorage jika selesai submit
    function closeAddModal() {
        document.getElementById('addAlbumModal').classList.add('hidden');
    }

    // --- ANTI-LOSS DRAFT AUTO SAVE SYSTEM (LOCALSTORAGE) ---
    const inputJudul = document.getElementById('draft_judul');
    const inputDeskripsi = document.getElementById('draft_deskripsi');

    // Load data lama jika tidak sengaja ter-refresh
    if (localStorage.getItem('cms_galeri_judul')) inputJudul.value = localStorage.getItem('cms_galeri_judul');
    if (localStorage.getItem('cms_galeri_deskripsi')) inputDeskripsi.value = localStorage.getItem('cms_galeri_deskripsi');

    // Dengar ketukan masukan user
    inputJudul.addEventListener('input', () => localStorage.setItem('cms_galeri_judul', inputJudul.value));
    inputDeskripsi.addEventListener('input', () => localStorage.setItem('cms_galeri_deskripsi', inputDeskripsi.value));

    // Bersihkan penampung draf lokal saat formulir sukses dikirim
    document.getElementById('addAlbumForm').onsubmit = function() {
        localStorage.removeItem('cms_galeri_judul');
        localStorage.removeItem('cms_galeri_deskripsi');
    };

    // --- WEB SYSTEM ALERT ENGINE ---
    function openSystemAlert(title, message, type = 'success', confirmAction = null) {
        const modal = document.getElementById('systemAlertModal');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const titleEl = document.getElementById('alertTitle');
        const descEl = document.getElementById('alertDescription');
        const actionArea = document.getElementById('alertActionArea');

        titleEl.innerText = title;
        descEl.innerText = message;
        iconContainer.className = "w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md";

        if (type === 'success') {
            iconContainer.classList.add('bg-green-100', 'text-green-600');
            icon.className = "fa-solid fa-circle-check";
            actionArea.innerHTML = `<button onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-sm">Oke</button>`;
        } else if (type === 'error') {
            iconContainer.classList.add('bg-red-100', 'text-red-600');
            icon.className = "fa-solid fa-triangle-exclamation";
            actionArea.innerHTML = `<button onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-sm">Tutup</button>`;
        } else if (type === 'confirm') {
            iconContainer.classList.add('bg-amber-100', 'text-amber-600');
            icon.className = "fa-solid fa-trash-can";
            actionArea.innerHTML = `
                <button onclick="closeSystemAlert()" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-white/60 border border-white text-[#3e2723] hover:bg-white/80 transition">Batal</button>
                <button id="confirmSubmitBtn" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-sm">Hapus</button>
            `;
            document.getElementById('confirmSubmitBtn').onclick = confirmAction;
        }
        modal.classList.remove('hidden');
    }

    function closeSystemAlert() {
        document.getElementById('systemAlertModal').classList.add('hidden');
    }

    function triggerDelete(actionUrl) {
        openSystemAlert(
            'Hapus Album Permanen',
            'Apakah Anda yakin ingin menghapus album portofolio ini beserta seluruh aset foto fisik di dalamnya?',
            'confirm',
            function() {
                const form = document.getElementById('globalDeleteForm');
                form.action = actionUrl;
                form.submit();
            }
        );
    }

    // Intersepsi Flash Session Laravel
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('success'))
            openSystemAlert('Berhasil!', "{{ session('success') }}", 'success');
        @endif
        @if($errors->any())
            openSystemAlert('Validasi Gagal', "{{ $errors->first() }}", 'error');
        @endif
    });
</script>
@endsection