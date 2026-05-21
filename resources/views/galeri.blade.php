<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>

        .glass{
            backdrop-filter: blur(22px);
            background: rgba(255,255,255,0.20);
        }

        .glass-soft{
            backdrop-filter: blur(16px);
            background: rgba(255,255,255,0.15);
        }

        .shine{
            position: relative;
            overflow: hidden;
        }

        .shine::before{
            content: "";
            position: absolute;
            top: 0;
            left: -130%;
            width: 80%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255,255,255,0.35),
                transparent
            );
            transition: 0.9s;
        }

        .shine:hover::before{
            left: 130%;
        }

        .glow-text{
            text-shadow:
                0 0 12px rgba(255,255,255,0.5),
                0 2px 12px rgba(0,0,0,0.25);
        }

        /* MOBILE SAFE */
        img{
            max-width: 100%;
        }

    </style>

</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723] overflow-x-hidden">

@include('layouts.navbar')

@php
use Illuminate\Support\Facades\File;
@endphp

<main class="relative overflow-hidden min-h-screen pt-20 md:pt-24">

    {{-- BACKGROUND --}}
    <div class="absolute inset-0 -z-10 overflow-hidden">

        <div class="absolute top-24 left-1/4 w-72 md:w-96 h-72 md:h-96 bg-[#c8a97e]/30 blur-3xl rounded-full"></div>

        <div class="absolute bottom-20 right-1/4 w-72 md:w-96 h-72 md:h-96 bg-[#3e2723]/20 blur-3xl rounded-full"></div>

        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-[#f3e8dc]/40 blur-3xl rounded-full"></div>

    </div>

    {{-- ================= TITLE ================= --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 md:pt-16 pb-12 text-center">

        <h1 class="text-3xl sm:text-4xl md:text-6xl font-black glow-text mb-4 break-words leading-tight">

            Galeri Edelweiss

        </h1>

        <p class="text-[#6b4f4f] text-sm sm:text-base md:text-lg max-w-2xl mx-auto">

            Momen dari dapur hingga hasil terbaik kami

        </p>

    </section>

    {{-- ================= CREATE ALBUM ================= --}}
    @auth
    @if(in_array(auth()->user()->role, ['admin','super_admin']))

    <section class="max-w-5xl mx-auto px-4 sm:px-6 mb-16 md:mb-20">

        <div class="glass border border-white/30 rounded-[2rem] p-5 sm:p-8 shadow-2xl shine w-full">

            <h2 class="text-2xl md:text-3xl font-bold glow-text mb-6 md:mb-8">

                + Tambah Album Baru

            </h2>

            <form
                method="POST"
                action="{{ route('galeri.store') }}"
                enctype="multipart/form-data"
                class="space-y-6">

                @csrf

                {{-- JUDUL --}}
                <div>

                    <label class="block mb-3 font-semibold text-base md:text-lg">

                        Judul Album

                    </label>

                    <input
                        type="text"
                        name="judul"
                        required
                        placeholder="Contoh: Proses Pembuatan Roti"
                        class="w-full px-5 py-4 rounded-2xl bg-white/30 border border-white/30 outline-none focus:ring-2 focus:ring-[#c8a97e] placeholder:text-[#8d6e63] text-sm md:text-base">

                </div>

                {{-- DESKRIPSI --}}
                <div>

                    <label class="block mb-3 font-semibold text-base md:text-lg">

                        Deskripsi

                    </label>

                    <textarea
                        name="deskripsi"
                        rows="4"
                        required
                        placeholder="Tuliskan deskripsi album..."
                        class="w-full px-5 py-4 rounded-2xl bg-white/30 border border-white/30 outline-none focus:ring-2 focus:ring-[#c8a97e] placeholder:text-[#8d6e63] text-sm md:text-base"></textarea>

                </div>

                {{-- UPLOAD --}}
                <div>

                    <label class="block mb-4 font-semibold text-base md:text-lg">

                        Upload Foto

                    </label>

                    <label class="flex flex-col items-center justify-center min-h-[180px] md:min-h-[250px] rounded-[2rem] border-2 border-dashed border-[#c8a97e]/40 bg-white/20 hover:bg-white/30 transition cursor-pointer p-4">

                        <div class="text-center">

                            <div class="text-5xl md:text-6xl mb-4">

                                📸

                            </div>

                            <h3 class="text-xl md:text-2xl font-bold mb-2">

                                Drag & Drop Foto

                            </h3>

                            <p class="text-[#6b4f4f] text-sm md:text-base">

                                atau klik untuk memilih gambar

                            </p>

                        </div>

                        <input type="file" name="gambar[]" multiple hidden>

                    </label>

                </div>

                {{-- BUTTON --}}
                <div class="pt-2">

                    <button
                        type="submit"
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-[#3e2723] to-[#6b4f4f] text-white font-semibold shadow-2xl hover:scale-105 transition duration-300">

                        Buat Album

                    </button>

                </div>

            </form>

        </div>

    </section>

    @endif
    @endauth

    {{-- ================= LIST ALBUM ================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-24 md:pb-28 space-y-12 md:space-y-16">

        @foreach($galeri as $album)

        @php

            $folderPath = public_path('img/galeri/' . $album->album);

            $files = [];

            if(File::exists($folderPath)){

                $files = collect(File::files($folderPath))
                    ->filter(function($file){

                        return in_array(
                            strtolower($file->getExtension()),
                            ['jpg','jpeg','png','webp']
                        );

                    })->values();

            }

        @endphp

        {{-- CARD --}}
        <div class="relative glass border border-white/30 rounded-[2rem] md:rounded-[2.5rem] p-4 sm:p-6 md:p-10 shadow-2xl shine">

            {{-- GLOW --}}
            <div class="absolute inset-0 rounded-[2rem] md:rounded-[2.5rem] bg-white/10 blur-2xl opacity-40"></div>

            <div class="relative z-10">

                {{-- DELETE --}}
                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))

                <form
                    method="POST"
                    action="{{ route('galeri.destroy', $album->id_galeri) }}"
                    class="absolute top-0 right-0">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        onclick="return confirm('Hapus album ini?')"
                        class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-red-500/90 text-white shadow-xl hover:scale-110 transition text-sm md:text-base">

                        ✕

                    </button>

                </form>

                @endif
                @endauth

                {{-- INFO --}}
                <div class="mb-8 pr-10 md:pr-14">

                    {{-- TITLE --}}
                    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 mb-4">

                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black glow-text break-words leading-tight">

                            {{ $album->judul }}

                        </h2>

                        @auth
                        @if(in_array(auth()->user()->role, ['admin','super_admin']))

                        <button
                            onclick="toggleEdit('editJudul{{ $album->id_galeri }}')"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl glass-soft border border-white/30 text-sm font-semibold hover:scale-105 transition">

                            Edit Judul

                        </button>

                        @endif
                        @endauth

                    </div>

                    {{-- DESKRIPSI --}}
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                        <p class="text-[#6b4f4f] leading-relaxed text-sm sm:text-base md:text-lg break-words whitespace-pre-wrap flex-1">

                            {{ $album->deskripsi }}

                        </p>

                        @auth
                        @if(in_array(auth()->user()->role, ['admin','super_admin']))

                        <button
                            onclick="toggleEdit('editDeskripsi{{ $album->id_galeri }}')"
                            class="w-full sm:w-auto px-3 py-2 rounded-xl glass-soft border border-white/30 text-sm font-semibold hover:scale-105 transition shrink-0">

                            Edit Deskripsi

                        </button>

                        @endif
                        @endauth

                    </div>

                </div>

                {{-- EDIT JUDUL --}}
                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))

                <form
                    id="editJudul{{ $album->id_galeri }}"
                    method="POST"
                    action="{{ route('galeri.update', $album->id_galeri) }}"
                    class="hidden mb-6">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="field" value="judul">

                    <div class="flex flex-col gap-4">

                        <input
                            type="text"
                            name="value"
                            value="{{ $album->judul }}"
                            class="w-full px-5 py-4 rounded-2xl bg-white/30 border border-white/30 outline-none focus:ring-2 focus:ring-[#c8a97e]">

                        <button
                            type="submit"
                            class="w-full md:w-auto px-6 py-4 rounded-2xl bg-[#3e2723] text-white font-semibold">

                            Simpan Judul

                        </button>

                    </div>

                </form>

                @endif
                @endauth

                {{-- EDIT DESKRIPSI --}}
                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))

                <form
                    id="editDeskripsi{{ $album->id_galeri }}"
                    method="POST"
                    action="{{ route('galeri.update', $album->id_galeri) }}"
                    class="hidden mb-8">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="field" value="deskripsi">

                    <div class="flex flex-col gap-4">

                        <textarea
                            name="value"
                            rows="4"
                            required
                            class="w-full px-5 py-4 rounded-2xl bg-white/30 border border-white/30 outline-none focus:ring-2 focus:ring-[#c8a97e]">{{ $album->deskripsi }}</textarea>

                        <button
                            type="submit"
                            class="w-full md:w-auto px-6 py-4 rounded-2xl bg-[#3e2723] text-white font-semibold">

                            Simpan Deskripsi

                        </button>

                    </div>

                </form>

                @endif
                @endauth

                {{-- ================= FOTO ================= --}}
                <div class="columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-3 md:gap-5">

                    @forelse($files as $file)

                    @php
                        $filename = basename($file);
                    @endphp

                    <div class="mb-3 md:mb-5 break-inside-avoid">

                        <div class="group relative overflow-hidden rounded-2xl md:rounded-[2rem] glass-soft border border-white/30 shadow-xl">

                            {{-- SHINE --}}
                            <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-700 z-10 pointer-events-none"></div>

                            {{-- IMAGE --}}
                            <img
                                src="{{ asset('img/galeri/'.$album->album.'/'.$filename) }}"
                                class="w-full h-auto block object-cover group-hover:scale-[1.03] transition duration-700"
                                loading="lazy"
                                alt="Galeri Foto">

                            {{-- OVERLAY --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-500 pointer-events-none"></div>

                            {{-- DELETE IMAGE --}}
                            @auth
                            @if(in_array(auth()->user()->role, ['admin','super_admin']))

                            <form
                                method="POST"
                                action="{{ route('galeri.update', $album->id_galeri) }}"
                                class="absolute top-2 right-2 md:top-3 md:right-3 z-20 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition duration-300">

                                @csrf
                                @method('PUT')

                                <input
                                    type="hidden"
                                    name="delete_image"
                                    value="{{ $filename }}">

                                <button
                                    type="submit"
                                    onclick="return confirm('Hapus foto ini?')"
                                    class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-red-500/90 text-white shadow-xl backdrop-blur-xl hover:scale-110 transition text-xs md:text-sm">

                                    ✕

                                </button>

                            </form>

                            @endif
                            @endauth

                        </div>

                    </div>

                    @empty

                    <div class="py-10 text-center">

                        <div class="inline-block px-6 py-4 rounded-2xl glass-soft border border-white/30 text-[#6b4f4f] text-sm md:text-base">

                            Belum ada foto di album ini

                        </div>

                    </div>

                    @endforelse

                </div>

                {{-- ================= ADD FOTO ================= --}}
                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))

                <div class="mt-8 md:mt-10">

                    <form
                        method="POST"
                        action="{{ route('galeri.update', $album->id_galeri) }}"
                        enctype="multipart/form-data"
                        class="glass-soft border border-white/30 rounded-[2rem] p-5 md:p-6">

                        @csrf
                        @method('PUT')

                        <h3 class="text-lg md:text-xl font-bold mb-5 glow-text">

                            + Tambah Foto

                        </h3>

                        <div class="flex flex-col gap-4">

                            <input
                                type="file"
                                name="gambar[]"
                                multiple
                                required
                                class="w-full text-sm
                                       file:w-full
                                       md:file:w-auto
                                       file:mb-3
                                       md:file:mb-0
                                       file:px-5
                                       file:py-3
                                       file:rounded-2xl
                                       file:border-0
                                       file:bg-[#3e2723]
                                       file:text-white
                                       file:font-semibold
                                       file:cursor-pointer">

                            <button
                                type="submit"
                                class="w-full md:w-auto px-8 py-3 rounded-2xl bg-gradient-to-r from-[#c8a97e] to-[#f3d19c] text-[#3e2723] font-bold shadow-xl hover:scale-105 transition">

                                Upload

                            </button>

                        </div>

                    </form>

                </div>

                @endif
                @endauth

            </div>

        </div>

        @endforeach

    </section>

</main>

<script>

function toggleEdit(id){

    const form = document.getElementById(id);

    if(form.classList.contains('hidden')){

        form.classList.remove('hidden');

    }else{

        form.classList.add('hidden');

    }

}

</script>

@include('layouts.footer')

</body>
</html>