<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk</title>

    <script src="https://cdn.tailwindcss.com"></script>

    {{-- SWIPER --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>

    <style>
        .glass{
            backdrop-filter: blur(20px);
            background: rgba(255,255,255,0.25);
        }
    </style>
</head>

<body class="bg-[#f5f0ea] text-[#3e2723]">

@include('layouts.navbar')

@php
    $folder = public_path('img/menu/' . $produk->gambar);
    $files = file_exists($folder) ? scandir($folder) : [];
    $images = array_values(array_diff($files, ['.', '..']));
@endphp

<main class="pt-20 md:pt-28 pb-24">

    {{-- ================= MOBILE STYLE TOKOPEDIA/SHOPEE ================= --}}
    <div class="max-w-6xl mx-auto">

        {{-- GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10">

            {{-- ================= LEFT : IMAGE ================= --}}
            <div class="lg:sticky lg:top-28 h-fit">

                {{-- SLIDER --}}
                <div class="bg-white shadow-lg overflow-hidden lg:rounded-3xl">

                    <div class="swiper mySwiper">

                        <div class="swiper-wrapper">

                            @foreach($images as $img)

                            <div class="swiper-slide">

                                <div class="w-full bg-[#f8f8f8] flex items-center justify-center">

                                    <img
                                        src="{{ asset('img/menu/' . $produk->gambar . '/' . $img) }}"
                                        class="w-full aspect-square object-cover"
                                    >

                                </div>

                            </div>

                            @endforeach

                        </div>

                        <div class="swiper-pagination"></div>

                    </div>

                </div>

                {{-- THUMBNAIL --}}
                <div class="flex gap-3 overflow-x-auto mt-4 px-4 lg:px-0">

                    @foreach($images as $img)

                    <div class="min-w-[80px] h-[80px] rounded-2xl overflow-hidden border border-white shadow-md">

                        <img
                            src="{{ asset('img/menu/' . $produk->gambar . '/' . $img) }}"
                            class="w-full h-full object-cover"
                        >

                    </div>

                    @endforeach

                </div>

            </div>

            {{-- ================= RIGHT : DETAIL ================= --}}
            <div class="px-4 lg:px-0">

                {{-- CARD --}}
                <div class="glass border border-white/30 shadow-xl rounded-3xl p-5 md:p-8">

                    {{-- BACK --}}
                    <a href="{{ route('menu') }}"
                       class="inline-flex items-center gap-2 mb-5 text-sm text-[#6b4f4f] hover:text-[#3e2723] transition">

                        ← Kembali ke Menu

                    </a>

                    {{-- TITLE --}}
                    <div class="mb-5">

                        <div class="flex items-start justify-between gap-4">

                            <h1 class="text-2xl md:text-4xl font-bold leading-tight">
                                {{ $produk->nama_produk }}
                            </h1>

                            @auth
                            @if(in_array(auth()->user()->role, ['admin','super_admin']))

                            <button
                                onclick="toggleEdit('nama')"
                                class="text-sm text-blue-500 whitespace-nowrap">

                                Edit

                            </button>

                            @endif
                            @endauth

                        </div>

                        {{-- FORM EDIT --}}
                        <form
                            id="edit-nama"
                            class="hidden mt-4"
                            method="POST"
                            action="{{ route('produk.update',$produk->id_produk) }}">

                            @csrf
                            @method('PUT')

                            <input type="hidden" name="field" value="nama_produk">

                            <div class="flex flex-col sm:flex-row gap-3">

                                <input
                                    type="text"
                                    name="value"
                                    value="{{ $produk->nama_produk }}"
                                    class="flex-1 px-4 py-3 rounded-xl border bg-white/70">

                                <button
                                    class="px-5 py-3 bg-[#3e2723] text-white rounded-xl">

                                    Simpan

                                </button>

                            </div>

                        </form>

                    </div>

                    {{-- PRICE --}}
                    <div class="mb-6">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-[#8b6f63] mb-1">
                                    Harga
                                </p>

                                <h2 class="text-3xl md:text-4xl font-black text-[#c8a97e]">

                                    Rp {{ number_format($produk->harga, 0, ',', '.') }}

                                </h2>

                            </div>

                            @auth
                            @if(in_array(auth()->user()->role, ['admin','super_admin']))

                            <button
                                onclick="toggleEdit('harga')"
                                class="text-sm text-blue-500">

                                Edit

                            </button>

                            @endif
                            @endauth

                        </div>

                        {{-- FORM EDIT --}}
                        <form
                            id="edit-harga"
                            class="hidden mt-4"
                            method="POST"
                            action="{{ route('produk.update',$produk->id_produk) }}">

                            @csrf
                            @method('PUT')

                            <input type="hidden" name="field" value="harga">

                            <div class="flex flex-col sm:flex-row gap-3">

                                <input
                                    type="number"
                                    name="value"
                                    value="{{ $produk->harga }}"
                                    class="flex-1 px-4 py-3 rounded-xl border bg-white/70">

                                <button
                                    class="px-5 py-3 bg-[#3e2723] text-white rounded-xl">

                                    Simpan

                                </button>

                            </div>

                        </form>

                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="border-t border-white/30 pt-6">

                        <div class="flex items-start justify-between gap-4 mb-4">

                            <h3 class="text-xl font-semibold">
                                Deskripsi Produk
                            </h3>

                            @auth
                            @if(in_array(auth()->user()->role, ['admin','super_admin']))

                            <button
                                onclick="toggleEdit('deskripsi')"
                                class="text-sm text-blue-500">

                                Edit

                            </button>

                            @endif
                            @endauth

                        </div>

                        <p class="text-[#6b4f4f] leading-relaxed text-sm md:text-base">

                            {{ $produk->deskripsi }}

                        </p>

                        {{-- FORM EDIT --}}
                        <form
                            id="edit-deskripsi"
                            class="hidden mt-4"
                            method="POST"
                            action="{{ route('produk.update',$produk->id_produk) }}">

                            @csrf
                            @method('PUT')

                            <input type="hidden" name="field" value="deskripsi">

                            <textarea
                                name="value"
                                rows="4"
                                class="w-full px-4 py-3 rounded-xl border bg-white/70">{{ $produk->deskripsi }}</textarea>

                            <button
                                class="mt-3 px-5 py-3 bg-[#3e2723] text-white rounded-xl">

                                Simpan

                            </button>

                        </form>

                    </div>

                </div>

                {{-- ================= ADMIN SECTION ================= --}}
                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))

                <div class="glass border border-white/30 shadow-xl rounded-3xl p-5 md:p-6 mt-6">

                    <h3 class="text-xl font-semibold mb-5">
                        Kelola Gambar
                    </h3>

                    {{-- UPLOAD --}}
                    <form
                        method="POST"
                        action="{{ route('produk.update',$produk->id_produk) }}"
                        enctype="multipart/form-data">

                        @csrf
                        @method('PUT')

                        <input
                            type="file"
                            name="gambar[]"
                            multiple
                            class="w-full text-sm mb-4
                                   file:mr-4
                                   file:px-4
                                   file:py-2
                                   file:rounded-xl
                                   file:border-0
                                   file:bg-[#3e2723]
                                   file:text-white">

                        <button
                            class="w-full sm:w-auto px-5 py-3 bg-[#3e2723] text-white rounded-xl shadow">

                            Upload Gambar

                        </button>

                    </form>

                    {{-- DELETE IMAGE --}}
                    <div class="flex flex-wrap gap-3 mt-6">

                        @foreach($images as $img)

                        <form
                            method="POST"
                            action="{{ route('produk.update',$produk->id_produk) }}">

                            @csrf
                            @method('PUT')

                            <input
                                type="hidden"
                                name="delete_image"
                                value="{{ $img }}">

                            <button
                                class="px-3 py-2 text-xs rounded-xl bg-red-500 text-white">

                                ✕ {{ $img }}

                            </button>

                        </form>

                        @endforeach

                    </div>

                </div>

                @endif
                @endauth

            </div>

        </div>

    </div>

</main>

@include('layouts.footer')

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

    document
        .getElementById('edit-' + type)
        .classList
        .toggle('hidden');

}

</script>

</body>
</html>