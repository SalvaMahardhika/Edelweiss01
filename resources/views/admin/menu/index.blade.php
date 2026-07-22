@extends('admin_layouts.master')

@section('page_title', 'Manajemen Produk Menu')

@section('content')
{{-- 💎 AREA SCROLL MANDIRI PADA PANEL KONTEN UTAMA --}}
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- HEADER MANAGEMENT INTERFACE --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#3e2723]">Daftar Produk Kue</h3>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data menu.</p>
        </div>
        <button onclick="openAddModal()" class="px-5 py-2.5 bg-[#3e2723] text-white font-semibold rounded-xl text-sm shadow-md hover:bg-[#2c1b18] transition duration-300 inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Produk Baru
        </button>
    </div>

    {{-- TABLE DATA KATALOG CMS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4 w-24 text-center">Foto</th>
                        <th class="px-6 py-4">Nama Produk</th>
                        <th class="px-6 py-4 text-center">Unggulan</th> <!-- 🆕 Kolom Baru -->
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    @foreach($produk as $item)
                    @php
                        $folder = public_path('img/menu/' . $item->gambar);
                        $files = file_exists($folder) ? array_values(array_diff(scandir($folder), ['.', '..'])) : [];
                    @endphp
                    <tr class="hover:bg-white/30 transition">
                        <td class="px-6 py-3 text-center">
                            <img src="{{ count($files) > 0 ? asset('img/menu/' . $item->gambar . '/' . $files[0]) : asset('img/logo/logo2.png') }}" 
                                 class="w-12 h-12 object-cover rounded-xl border border-white bg-gray-100 mx-auto shadow-sm">
                        </td>
                        <td class="px-6 py-3">
                            <p class="font-bold text-[#2d1f1b]">{{ $item->nama_produk }}</p>
                            <p class="text-xs text-gray-400 line-clamp-1 max-w-xs font-normal">{{ $item->deskripsi }}</p>
                        </td>
                        
                        {{-- 🆕 TOMBOL TOGGLE UNGGULAN --}}
                        <td class="px-6 py-3 text-center">
                            <form method="POST" action="{{ route('produk.toggleStatus', $item->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="field" value="is_featured">
                                <button type="submit" class="relative inline-flex items-center h-6 w-12 rounded-full transition-colors duration-300 border border-white/30 {{ $item->is_featured ? 'bg-amber-500' : 'bg-gray-300' }}">
                                    <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-300 {{ $item->is_featured ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>

                        {{-- Slot PO (Status Aktif) --}}
                        <td class="px-6 py-3 text-center">
                            <form method="POST" action="{{ route('produk.toggleStatus', $item->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="relative inline-flex items-center h-6 w-12 rounded-full transition-colors duration-300 border border-white/30 {{ $item->status ? 'bg-green-500' : 'bg-red-500' }}">
                                    <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-300 {{ $item->status ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>
                        
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('produk.edit', $item->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Edit Data & Album">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <button type="button" onclick="triggerDelete('{{ route('produk.destroy', $item->id) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Hapus Produk">
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

{{-- 🔒 COMPONENT: MODAL POPUP TAMBAH PRODUK --}}
<div id="addProductModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-lg p-6 rounded-[2rem] bg-white/40 backdrop-blur-3xl border border-white/50 shadow-2xl relative space-y-4 my-auto">
        <div class="flex justify-between items-center pb-2 border-b border-[#3e2723]/15">
            <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-solid fa-cake-candles mr-2"></i> Form Input Menu Baru</h3>
            <button type="button" onclick="closeAddModal()" class="w-8 h-8 rounded-full bg-white/40 flex items-center justify-center font-bold text-[#3e2723] hover:bg-white/70">✕</button>
        </div>

        <form method="POST" action="{{ route('produk.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Nama Produk Kue</label>
                <input type="text" name="nama_produk" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Kategori</label>
                    <select name="category_id" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition text-[#3e2723] shadow-inner">
                        <option value="" disabled selected class="text-gray-400">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" class="bg-white text-[#3e2723]">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Harga (Rp)</label>
                    <input type="number" name="harga" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Deskripsi Komposisi</label>
                <textarea name="deskripsi" rows="3" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition"></textarea>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Upload Foto Galeri (Bisa Multiple)</label>
                <input type="file" name="gambar[]" multiple required class="w-full mt-1 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#3e2723] file:text-white file:cursor-pointer">
                <p class="text-[10px] text-gray-400 mt-1">*Gambar akan otomatis di-compress dan di-convert ke format modern WebP.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeAddModal()" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-white/60 border border-white/40 text-[#3e2723] hover:bg-white/80 transition">Batal</button>
                <button type="submit" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-[#3e2723] text-white shadow-md hover:bg-[#2c1b18] transition">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

{{-- 🚨 CUSTOM GLOBAL SYSTEM MODAL ALERT (SUCCESS / ERROR / CONFIRMATION) --}}
<div id="systemAlertModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl text-center space-y-4">
        {{-- Dynamic Icon Container --}}
        <div id="alertIconContainer" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md">
            <i id="alertIcon" class="fa-solid"></i>
        </div>
        {{-- Text Output --}}
        <div>
            <h4 id="alertTitle" class="text-lg font-bold text-[#3e2723]">Notifikasi</h4>
            <p id="alertDescription" class="text-sm text-gray-600 mt-1 leading-relaxed"></p>
        </div>
        {{-- Action Buttons --}}
        <div id="alertActionArea" class="flex gap-2 pt-2">
            <button id="alertCloseBtn" onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-sm">
                Selesai
            </button>
        </div>
    </div>
</div>

{{-- Hidden Form for Delete Action --}}
<form id="globalDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // --- MODAL CONTROL FORM TAMBAH ---
    function openAddModal() {
        document.getElementById('addProductModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addProductModal').classList.add('hidden');
    }

    // --- SYSTEM CUSTOM ALERT MODULE ---
    function openSystemAlert(title, message, type = 'success', confirmAction = null) {
        const modal = document.getElementById('systemAlertModal');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const titleEl = document.getElementById('alertTitle');
        const descEl = document.getElementById('alertDescription');
        const actionArea = document.getElementById('alertActionArea');

        titleEl.innerText = title;
        descEl.innerText = message;

        // Reset UI classes
        iconContainer.className = "w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md";
        actionArea.innerHTML = '';

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
                <button id="alertConfirmSubmitBtn" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-sm">Hapus</button>
            `;
            // Pasang event ke tombol konfirmasi
            document.getElementById('alertConfirmSubmitBtn').onclick = confirmAction;
        }

        modal.classList.remove('hidden');
    }

    function closeSystemAlert() {
        document.getElementById('systemAlertModal').classList.add('hidden');
    }

    // Handler Penghapusan Kue Custom
    function triggerDelete(actionUrl) {
        openSystemAlert(
            'Konfirmasi Hapus',
            'Apakah Anda yakin ingin menghapus menu kue ini secara permanen dari database?',
            'confirm',
            function() {
                const form = document.getElementById('globalDeleteForm');
                form.action = actionUrl;
                form.submit();
            }
        );
    }

    // --- INTERSEPSI SESSION LARAVEL AUTO FLUSH ---
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('success'))
            openSystemAlert('Berhasil!', "{{ session('success') }}", 'success');
        @endif

        @if($errors->any())
            openSystemAlert('Gagal Validasi', "{{ $errors->first() }}", 'error');
        @endif
    });
</script>
@endsection