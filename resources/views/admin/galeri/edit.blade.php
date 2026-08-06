@extends('admin_layouts.master')

@section('page_title', 'Edit Album Galeri')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex items-center justify-between">
        <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-regular fa-images mr-2"></i> Perbarui Isi Album Portofolio</h3>
        <a href="{{ route('galeri.index') }}" class="px-4 py-2 text-xs font-bold rounded-xl bg-white/60 border border-white text-[#3e2723] shadow-sm hover:bg-[#3e2723] hover:text-white transition">
            ← Kembali ke Tabel
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4 h-[calc(100vh-16rem)] overflow-y-auto pr-2">
            <h4 class="text-xs font-bold text-[#3e2723]/80 uppercase tracking-wide">Daftar Gambar Foto</h4>
            
            @php
                // PENGECEKAN PATH DYNAMIC UNTUK PUBLIC_HTML HOSTING DAN LOCALHOST
                $publicHtmlFolder = base_path('../public_html/img/galeri/' . $galeri->album);
                $localFolder = public_path('img/galeri/' . $galeri->album);

                if (file_exists($publicHtmlFolder)) {
                    $folder = $publicHtmlFolder;
                } else {
                    $folder = $localFolder;
                }

                $files = file_exists($folder) ? array_values(array_diff(scandir($folder), ['.', '..'])) : [];
            @endphp

            <div class="grid grid-cols-2 gap-2">
                @forelse($files as $img)
                <div class="relative group rounded-xl overflow-hidden border border-white shadow-sm aspect-square bg-gray-100">
                    <img src="{{ asset('img/galeri/' . $galeri->album . '/' . $img) }}" class="w-full h-full object-cover">
                    
                    {{-- Tombol Hapus dengan Trigger Kustom --}}
                    <button type="button" 
                            onclick="triggerDeletePhoto('{{ route('galeri.update', $galeri->id) }}', '{{ $img }}')" 
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-300">
                        <span class="w-8 h-8 rounded-full bg-red-600 text-white text-xs flex items-center justify-center shadow hover:scale-110 transition">✕</span>
                    </button>
                </div>
                @empty
                <p class="text-xs text-gray-400 col-span-2 text-center py-8">Belum diunggah foto.</p>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-2 backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl">
            <form id="editAlbumForm" method="POST" action="{{ route('galeri.update', $galeri->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Judul Album</label>
                    <input type="text" id="edit_judul" name="judul" value="{{ $galeri->judul }}" required class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">
                </div>

                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Deskripsi Narasi Dokumentasi</label>
                    <textarea id="edit_deskripsi" name="deskripsi" rows="5" required class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">{{ $galeri->deskripsi }}</textarea>
                </div>

                <div class="pt-2 border-t border-white/30">
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Tambahkan Gambar Baru</label>
                    <input type="file" name="gambar[]" multiple class="w-full mt-1.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#3e2723] file:text-white file:cursor-pointer">
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#3e2723] text-white font-bold rounded-xl shadow-md hover:bg-[#2c1b18] transition duration-300 text-sm">
                    Simpan Seluruh Perubahan
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Hidden Form untuk Hapus Foto Satuan --}}
<form id="deletePhotoForm" method="POST" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="delete_image" id="delete_image_input">
</form>

{{-- MODAL ALERT KUSTOM --}}
<div id="systemAlertModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl text-center space-y-4">
        <div id="alertIconContainer" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md">
            <i id="alertIcon" class="fa-solid"></i>
        </div>
        <div>
            <h4 id="alertTitle" class="text-lg font-bold text-[#3e2723]">Notifikasi</h4>
            <p id="alertDescription" class="text-sm text-gray-600 mt-1"></p>
        </div>
        <div id="alertActionArea" class="flex gap-2 pt-2"></div>
    </div>
</div>

<script>
    // --- PERSIST DRAFT ---
    const albumId = "{{ $galeri->id }}";
    const editJudul = document.getElementById('edit_judul');
    const editDeskripsi = document.getElementById('edit_deskripsi');

    if(localStorage.getItem('edit_galeri_judul_' + albumId)) editJudul.value = localStorage.getItem('edit_galeri_judul_' + albumId);
    if(localStorage.getItem('edit_galeri_deskripsi_' + albumId)) editDeskripsi.value = localStorage.getItem('edit_galeri_deskripsi_' + albumId);

    editJudul.addEventListener('input', () => localStorage.setItem('edit_galeri_judul_' + albumId, editJudul.value));
    editDeskripsi.addEventListener('input', () => localStorage.setItem('edit_galeri_deskripsi_' + albumId, editDeskripsi.value));
    document.getElementById('editAlbumForm').onsubmit = () => {
        localStorage.removeItem('edit_galeri_judul_' + albumId);
        localStorage.removeItem('edit_galeri_deskripsi_' + albumId);
    };

    // --- ALERT MODAL ---
    function openSystemAlert(title, message, type, confirmAction = null) {
        document.getElementById('systemAlertModal').classList.remove('hidden');
        document.getElementById('alertTitle').innerText = title;
        document.getElementById('alertDescription').innerText = message;
        const iconC = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const area = document.getElementById('alertActionArea');
        area.innerHTML = '';
        iconC.className = "w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md";

        if(type === 'confirm') {
            iconC.classList.add('bg-amber-100', 'text-amber-600');
            icon.className = "fa-solid fa-trash-can";
            area.innerHTML = `<button onclick="closeSystemAlert()" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-white/60 border border-white text-[#3e2723]">Batal</button>
                             <button id="confirmBtn" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white">Hapus</button>`;
            document.getElementById('confirmBtn').onclick = confirmAction;
        } else {
            iconC.classList.add('bg-green-100', 'text-green-600');
            icon.className = "fa-solid fa-circle-check";
            area.innerHTML = `<button onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-[#3e2723] text-white">Oke</button>`;
        }
    }

    function closeSystemAlert() { document.getElementById('systemAlertModal').classList.add('hidden'); }

    function triggerDeletePhoto(url, filename) {
        openSystemAlert('Hapus Foto', 'Yakin ingin menghapus foto ini?', 'confirm', () => {
            document.getElementById('delete_image_input').value = filename;
            const form = document.getElementById('deletePhotoForm');
            form.action = url;
            form.submit();
        });
    }
</script>
@endsection