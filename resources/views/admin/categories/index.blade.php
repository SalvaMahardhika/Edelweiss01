@extends('admin_layouts.master')

@section('page_title', 'Manajemen Kategori')

@section('content')
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- HEADER --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#3e2723]">Kategori Produk</h3>
            <p class="text-sm text-gray-500 mt-0.5">Atur pengelompokan menu dan status visibilitasnya.</p>
        </div>
        <button onclick="openModal()" class="px-5 py-2.5 bg-[#3e2723] text-white font-semibold rounded-xl text-sm shadow-md hover:bg-[#2c1b18] transition inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
    </div>

    {{-- TABEL --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4">Nama Kategori</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4 text-center">Status (Aktif)</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    @foreach($categories as $cat)
                    <tr class="hover:bg-white/30 transition">
                        <td class="px-6 py-4 font-bold text-[#3e2723]">{{ $cat->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $cat->description }}</td>
                        <td class="px-6 py-4 text-center">
                            <form method="POST" action="{{ route('kategori.toggle', $cat->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="relative inline-flex items-center h-6 w-12 rounded-full transition-colors duration-300 border border-white/30 {{ $cat->is_active ? 'bg-green-500' : 'bg-red-500' }}">
                                    <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-300 {{ $cat->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="triggerDelete('{{ route('kategori.destroy', $cat->id) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition shadow-sm bg-white/60 border border-white">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/40 backdrop-blur-3xl border border-white/50 shadow-2xl relative">
        <h3 class="text-lg font-bold text-[#3e2723] mb-4">Tambah Kategori</h3>
        <form method="POST" action="{{ route('kategori.store') }}" class="space-y-4">
            @csrf
            <input type="text" name="name" placeholder="Nama Kategori" required class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:ring-2 focus:ring-[#c8a97e] outline-none">
            <textarea name="description" placeholder="Deskripsi Singkat" class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:ring-2 focus:ring-[#c8a97e] outline-none"></textarea>
            <div class="flex gap-2">
                <button type="button" onclick="closeModal()" class="flex-1 py-2 rounded-xl bg-white/50">Batal</button>
                <button type="submit" class="flex-1 py-2 rounded-xl bg-[#3e2723] text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- SISTEM ALERT MODAL (Sama dengan Dashboard) --}}
<div id="systemAlertModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl text-center space-y-4">
        <div id="alertIconContainer" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md bg-green-100 text-green-600">
            <i id="alertIcon" class="fa-solid fa-check"></i>
        </div>
        <p id="alertDescription" class="text-sm text-gray-700"></p>
        <div id="alertActionArea" class="flex gap-2 pt-2">
            <button onclick="closeSystemAlert()" class="w-full py-2.5 font-bold rounded-xl bg-[#3e2723] text-white">Oke</button>
        </div>
    </div>
</div>

<form id="globalDeleteForm" method="POST" class="hidden">@csrf @method('DELETE')</form>

<script>
    function openModal() { document.getElementById('addModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('addModal').classList.add('hidden'); }
    function closeSystemAlert() { document.getElementById('systemAlertModal').classList.add('hidden'); }
    
    function triggerDelete(url) {
        document.getElementById('systemAlertModal').classList.remove('hidden');
        document.getElementById('alertDescription').innerText = "Yakin ingin menghapus kategori ini?";
        document.getElementById('alertActionArea').innerHTML = `
            <button onclick="closeSystemAlert()" class="flex-1 py-2 rounded-xl bg-white/50">Batal</button>
            <button id="confirmBtn" class="flex-1 py-2 rounded-xl bg-red-600 text-white">Hapus</button>`;
        document.getElementById('confirmBtn').onclick = () => {
            const f = document.getElementById('globalDeleteForm');
            f.action = url; f.submit();
        };
    }
</script>
@endsection