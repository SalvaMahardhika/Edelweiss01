<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Produk</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>

    <style>
        .glass {
            backdrop-filter: blur(20px);
            background: rgba(255,255,255,0.25);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723]">

@include('layouts.navbar')

<main class="pt-32 max-w-4xl mx-auto px-6 pb-20">

    {{-- BACK --}}
    <a href="{{ route('menu') }}" 
       class="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-xl bg-white/40 backdrop-blur-xl border border-white/30 shadow hover:scale-105 transition">
        ← Kembali ke Menu
    </a>

    @php
        $folder = public_path('img/menu/' . $produk->gambar);
        $files = file_exists($folder) ? scandir($folder) : [];
        $images = array_values(array_diff($files, ['.', '..']));
    @endphp

    {{-- ================= SLIDER ================= --}}
    <div class="glass border border-white/40 rounded-3xl shadow-xl p-6 mb-6">

        <div class="swiper mySwiper">

            <div class="swiper-wrapper">

                @foreach($images as $img)
                <div class="swiper-slide flex justify-center items-center">

                    {{-- 🔥 WRAPPER FLEXIBLE --}}
                    <div class="bg-white/20 backdrop-blur-xl rounded-2xl p-4 shadow-lg flex justify-center items-center">

                        <img src="{{ asset('img/menu/' . $produk->gambar . '/' . $img) }}"
                             class="w-auto h-auto max-h-[500px] object-contain">

                    </div>

                </div>
                @endforeach

            </div>

            <div class="swiper-pagination mt-4"></div>

        </div>

        {{-- ================= UPLOAD (TIDAK DIUBAH LOGIC) ================= --}}
        @auth
        @if(in_array(auth()->user()->role, ['admin','super_admin']))

        <form method="POST" action="{{ route('produk.update',$produk->id_produk) }}" enctype="multipart/form-data"
              class="mt-6 border border-white/30 rounded-xl p-4 bg-white/20 backdrop-blur-xl">

            @csrf
            @method('PUT')

            <label class="block text-sm mb-2 font-medium">Tambah Gambar</label>

            <input type="file" name="gambar[]" multiple 
                class="w-full text-sm mb-3 file:mr-4 file:px-3 file:py-1 file:rounded-lg file:border-0 file:bg-[#3e2723] file:text-white hover:file:bg-[#2c1b18]">

            <button class="px-4 py-2 bg-[#3e2723] text-white rounded-lg shadow hover:bg-[#2c1b18] transition">
                Upload
            </button>
        </form>

        {{-- DELETE IMAGE --}}
        <div class="flex flex-wrap gap-2 mt-4">
            @foreach($images as $img)
            <form method="POST" action="{{ route('produk.update',$produk->id_produk) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="delete_image" value="{{ $img }}">

                <button class="px-3 py-1 text-xs bg-red-500/80 text-white rounded-lg hover:bg-red-600 transition">
                    ✕ {{ $img }}
                </button>
            </form>
            @endforeach
        </div>

        @endif
        @endauth

    </div>

    {{-- ================= DETAIL ================= --}}
    <div class="glass border border-white/40 rounded-3xl shadow-xl p-6">

        {{-- NAMA --}}
        <div class="mb-5">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">{{ $produk->nama_produk }}</h1>

                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))
                <button onclick="toggleEdit('nama')" class="text-sm text-blue-500">Edit</button>
                @endif
                @endauth
            </div>

            <form id="edit-nama" class="hidden mt-3" method="POST" action="{{ route('produk.update',$produk->id_produk) }}">
                @csrf @method('PUT')
                <input type="hidden" name="field" value="nama_produk">

                <div class="flex gap-2">
                    <input type="text" name="value" value="{{ $produk->nama_produk }}"
                        class="flex-1 px-3 py-2 rounded-lg border bg-white/60 backdrop-blur">
                    <button class="px-3 bg-[#3e2723] text-white rounded-lg">✔</button>
                </div>
            </form>
        </div>

        {{-- HARGA --}}
        <div class="mb-5">
            <div class="flex justify-between items-center">
                <p class="text-xl text-[#c8a97e] font-semibold">
                    Rp {{ number_format($produk->harga, 0, ',', '.') }}
                </p>

                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))
                <button onclick="toggleEdit('harga')" class="text-sm text-blue-500">Edit</button>
                @endif
                @endauth
            </div>

            <form id="edit-harga" class="hidden mt-3" method="POST" action="{{ route('produk.update',$produk->id_produk) }}">
                @csrf @method('PUT')
                <input type="hidden" name="field" value="harga">

                <div class="flex gap-2">
                    <input type="number" name="value" value="{{ $produk->harga }}"
                        class="flex-1 px-3 py-2 rounded-lg border bg-white/60">
                    <button class="px-3 bg-[#3e2723] text-white rounded-lg">✔</button>
                </div>
            </form>
        </div>

        {{-- DESKRIPSI --}}
        <div>
            <div class="flex justify-between items-start">
                <p class="text-[#6b4f4f] leading-relaxed">
                    {{ $produk->deskripsi }}
                </p>

                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))
                <button onclick="toggleEdit('deskripsi')" class="text-sm text-blue-500">Edit</button>
                @endif
                @endauth
            </div>

            <form id="edit-deskripsi" class="hidden mt-3" method="POST" action="{{ route('produk.update',$produk->id_produk) }}">
                @csrf @method('PUT')
                <input type="hidden" name="field" value="deskripsi">

                <textarea name="value" rows="3"
                    class="w-full px-3 py-2 rounded-lg border bg-white/60">{{ $produk->deskripsi }}</textarea>

                <button class="mt-2 px-3 py-1 bg-[#3e2723] text-white rounded-lg">✔ Simpan</button>
            </form>
        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

<script>
new Swiper(".mySwiper", {
    loop: true,
    spaceBetween: 20,
    pagination: {
        el: ".swiper-pagination",
        clickable: true
    }
});

function toggleEdit(type){
    document.getElementById('edit-'+type).classList.toggle('hidden');
}
</script>

</body>
</html>