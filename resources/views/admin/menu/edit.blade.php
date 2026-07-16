@extends('admin_layouts.master')

@section('page_title', 'Edit Menu')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex items-center justify-between">
        <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-regular fa-pen-to-square mr-2"></i> Perbarui Data Produk</h3>
        <a href="{{ route('produk.index') }}" class="px-4 py-2 text-xs font-bold rounded-xl bg-white/60 border border-white text-[#3e2723] shadow-sm hover:bg-[#3e2723] hover:text-white transition">
            ← Kembali ke Tabel
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- PANEL KIRI: ALBUM FOTO --}}
        <div class="md:col-span-1 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4 h-fit">
            <h4 class="text-xs font-bold text-[#3e2723]/80 uppercase tracking-wide">Album Foto Produk</h4>
            
            @php
                $folder = public_path('img/menu/' . $produk->gambar);
                $files = file_exists($folder) ? array_values(array_diff(scandir($folder), ['.', '..'])) : [];
            @endphp

            <div class="grid grid-cols-2 gap-2">
                @forelse($files as $img)
                <div class="relative group rounded-xl overflow-hidden border border-white shadow-sm">
                    <img src="{{ asset('img/menu/' . $produk->gambar . '/' . $img) }}" class="w-full aspect-square object-cover">
                    
                    {{-- Trigger Hapus Foto Kustom --}}
                    <button type="button" onclick="triggerDeletePhoto('{{ route('produk.update', $produk->id) }}', '{{ $img }}')" 
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-300">
                        <span class="w-8 h-8 rounded-full bg-red-600 text-white text-xs flex items-center justify-center shadow hover:scale-110 transition">✕</span>
                    </button>
                </div>
                @empty
                <p class="text-xs text-gray-400 col-span-2 text-center py-4">Belum ada foto.</p>
                @endforelse
            </div>
        </div>

        {{-- FORM UPDATE --}}
        <div class="md:col-span-2 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl">
            <form id="editMenuForm" method="POST" action="{{ route('produk.update', $produk->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Nama Kue / Roti</label>
                    <input type="text" id="draft_nama" name="nama_produk" value="{{ $produk->nama_produk }}" required class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Kategori</label>
                        <select id="draft_kategori" name="category_id" required class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition text-[#3e2723] shadow-inner">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $produk->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Harga Jual (Rp)</label>
                        <input type="number" id="draft_harga" name="harga" value="{{ $produk->harga }}" required class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Deskripsi Produk</label>
                    <textarea id="draft_deskripsi" name="deskripsi" rows="4" required class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">{{ $produk->deskripsi }}</textarea>
                </div>

                <div class="pt-2 border-t border-white/30">
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tambah Gambar Baru</label>
                    <input type="file" name="gambar[]" multiple class="w-full mt-1.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#3e2723] file:text-white file:cursor-pointer">
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#3e2723] text-white font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition duration-300 text-sm">
                    Simpan Seluruh Perubahan
                </button>
            </form>
        </div>
    </div>
</div>

{{-- MODAL ALERT --}}
<div id="systemAlertModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl text-center space-y-4">
        <div id="alertIconContainer" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md">
            <i id="alertIcon" class="fa-solid"></i>
        </div>
        <p id="alertDescription" class="text-sm text-gray-700"></p>
        <div id="alertActionArea" class="flex gap-2 pt-2"></div>
    </div>
</div>

<form id="deletePhotoForm" method="POST" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="delete_image" id="delete_image_input">
</form>

<script>
    // 1. DRAFT AUTO-SAVE (LOCAL STORAGE)
    const productId = "{{ $produk->id }}";
    const inputs = { nama: 'draft_nama', kategori: 'draft_kategori', harga: 'draft_harga', deskripsi: 'draft_deskripsi' };
    
    Object.keys(inputs).forEach(key => {
        const el = document.getElementById(inputs[key]);
        if(localStorage.getItem('edit_' + key + '_' + productId)) el.value = localStorage.getItem('edit_' + key + '_' + productId);
        el.addEventListener('input', () => localStorage.setItem('edit_' + key + '_' + productId, el.value));
    });

    document.getElementById('editMenuForm').onsubmit = () => {
        Object.keys(inputs).forEach(key => localStorage.removeItem('edit_' + key + '_' + productId));
    };

    // 2. MODAL ALERT SISTEM
    function triggerDeletePhoto(url, filename) {
        document.getElementById('systemAlertModal').classList.remove('hidden');
        document.getElementById('alertDescription').innerText = "Yakin ingin menghapus foto ini?";
        document.getElementById('alertActionArea').innerHTML = `
            <button onclick="closeModal()" class="flex-1 py-2 rounded-xl bg-gray-200">Batal</button>
            <button id="confirmBtn" class="flex-1 py-2 rounded-xl bg-red-600 text-white">Hapus</button>
        `;
        document.getElementById('confirmBtn').onclick = () => {
            document.getElementById('delete_image_input').value = filename;
            const form = document.getElementById('deletePhotoForm');
            form.action = url;
            form.submit();
        };
    }
    function closeModal() { document.getElementById('systemAlertModal').classList.add('hidden'); }
</script>
@endsection