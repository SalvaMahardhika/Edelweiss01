<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Edelweiss Bakery</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723]">

@include('layouts.navbar')

@php
use Illuminate\Support\Facades\File;
@endphp

<main class="relative overflow-hidden">

    {{-- Background Glow COKLAT --}}
    <div class="absolute inset-0 -z-10">

        {{-- gold warm --}}
        <div class="absolute top-32 left-1/4 w-96 h-96 bg-[#c8a97e]/30 blur-3xl rounded-full"></div>

        {{-- dark brown --}}
        <div class="absolute bottom-32 right-1/4 w-96 h-96 bg-[#3e2723]/25 blur-3xl rounded-full"></div>

        {{-- cream soft --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#f3e8dc]/40 blur-3xl rounded-full"></div>

    </div>

    {{-- tambahan depth --}}
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(62,39,35,0.05),transparent_70%)] -z-10"></div>

    {{-- TITLE --}}
    <section class="max-w-6xl mx-auto px-6 py-20 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Galeri Edelweiss</h1>
        <p class="text-[#6b4f4f]">Momen dari dapur hingga hasil terbaik kami</p>
    </section>

    {{-- LIST ALBUM --}}
    <section class="max-w-6xl mx-auto px-6 pb-24 space-y-16">

        @foreach($galeri as $album)

        @php
            $folderPath = public_path('img/galeri/' . $album->album);
            $files = [];

            if (File::exists($folderPath)) {
                $files = collect(File::files($folderPath))->filter(function ($file) {
                    return in_array(strtolower($file->getExtension()), ['jpg','jpeg','png','webp']);
                })->values();
            }
        @endphp

        {{-- ALBUM CARD --}}
        <div class="relative backdrop-blur-2xl bg-[#ffffff]/40 border border-white/40 rounded-3xl p-8 shadow-xl">

            {{-- glow layer --}}
            <div class="absolute inset-0 rounded-3xl bg-white/20 opacity-20 blur-xl"></div>

            <div class="relative">

                {{-- JUDUL --}}
                <h2 class="text-2xl font-semibold mb-2">
                    {{ $album->judul }}
                </h2>

                {{-- DESKRIPSI --}}
                <p class="text-[#6b4f4f] text-sm mb-8">
                    {{ $album->deskripsi }}
                </p>

                {{-- FOTO (MASONRY FLEXIBLE) --}}
                <div class="columns-1 sm:columns-2 md:columns-3 lg:columns-4 gap-5">

                    @forelse($files as $file)

                        <div class="mb-5 break-inside-avoid">

                            <div class="rounded-2xl overflow-hidden bg-[#f5ede4]/50 backdrop-blur-md shadow-md hover:shadow-xl transition duration-300">

                                <img 
                                    src="{{ asset('img/galeri/'.$album->album.'/'.basename($file)) }}" 
                                    class="w-full h-auto object-cover hover:scale-[1.04] transition duration-500"
                                    loading="lazy"
                                >

                            </div>

                        </div>

                    @empty
                        <p class="text-center text-[#8d6e63]">
                            Belum ada foto di album ini
                        </p>
                    @endforelse

                </div>

            </div>

        </div>

        @endforeach

    </section>

</main>

@include('layouts.footer')

</body>
</html>