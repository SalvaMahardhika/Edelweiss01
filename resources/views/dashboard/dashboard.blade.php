<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edelweiss Bakery</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Icon --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body class="bg-[#fafafa] text-[#3e2723] overflow-x-hidden">

    {{-- NAVBAR --}}
    @include('layouts.navbar')

    {{-- ================= HERO ================= --}}
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">

        {{-- BACKGROUND --}}
        <div class="absolute inset-0">

            <img src="{{ asset('img/dashboard/assets/3.png') }}"
                 class="w-full h-full object-cover">

            <div class="absolute inset-0 bg-black/45"></div>

        </div>

        {{-- GLOW --}}
        <div class="absolute inset-0 overflow-hidden">

            <div class="absolute top-1/3 left-1/4
                        w-52 sm:w-72 md:w-80
                        h-52 sm:h-72 md:h-80
                        bg-[#d4af37]
                        rounded-full
                        blur-3xl
                        opacity-20">
            </div>

            <div class="absolute bottom-1/4 right-1/4
                        w-52 sm:w-72 md:w-80
                        h-52 sm:h-72 md:h-80
                        bg-[#3e2723]
                        rounded-full
                        blur-3xl
                        opacity-20">
            </div>

        </div>

        {{-- CONTENT --}}
        <div class="relative z-10 w-full max-w-5xl mx-auto px-4 sm:px-6">

            <div class="backdrop-blur-2xl
                        bg-white/10
                        border border-white/20
                        rounded-[2rem]
                        sm:rounded-[2.5rem]
                        p-6 sm:p-10 md:p-14
                        text-center
                        shadow-2xl">

                {{-- TITLE --}}
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl
                           font-black
                           text-white
                           leading-tight
                           mb-6">

                    Kelezatan yang
                    <br class="hidden sm:block">
                    Tumbuh dari Keindahan

                </h1>

                {{-- DESC --}}
                <p class="text-white/80
                          text-sm sm:text-base md:text-lg
                          leading-relaxed
                          max-w-2xl
                          mx-auto
                          mb-8">

                    Roti rumahan yang terinspirasi dari
                    keindahan bunga Edelweiss.

                </p>

                {{-- BUTTON --}}
                <div class="flex flex-col sm:flex-row
                            gap-4
                            justify-center
                            items-center">

                    <a href="{{ route('menu') }}"
                       class="w-full sm:w-auto
                              px-8 py-4
                              rounded-2xl
                              bg-[#3e2723]
                              text-white
                              font-semibold
                              shadow-xl
                              hover:bg-[#2c1b18]
                              hover:scale-105
                              transition duration-300
                              text-center">

                        Lihat Menu

                    </a>

                    <a href="{{ route('kontak') }}"
                       class="w-full sm:w-auto
                              px-8 py-4
                              rounded-2xl
                              bg-white/10
                              backdrop-blur-xl
                              border border-white/20
                              text-white
                              font-semibold
                              hover:bg-white/20
                              hover:scale-105
                              transition duration-300
                              text-center">

                        Kontak

                    </a>

                </div>

            </div>

        </div>

    </section>

    {{-- ================= PRODUK ================= --}}
    <section class="relative py-20 sm:py-24 overflow-hidden">

        {{-- BACKGROUND --}}
        <div class="absolute inset-0
                    bg-gradient-to-br
                    from-[#fafafa]
                    via-[#f5efe8]
                    to-[#ede5dc]">
        </div>

        {{-- GLOW --}}
        <div class="absolute inset-0 overflow-hidden">

            <div class="absolute top-20 left-1/3
                        w-52 sm:w-72
                        h-52 sm:h-72
                        bg-[#d4af37]/20
                        blur-3xl
                        rounded-full">
            </div>

            <div class="absolute bottom-10 right-1/4
                        w-52 sm:w-72
                        h-52 sm:h-72
                        bg-[#3e2723]/20
                        blur-3xl
                        rounded-full">
            </div>

        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6">

            {{-- TITLE --}}
            <div class="text-center mb-14 sm:mb-16">

                <h2 class="text-3xl sm:text-4xl font-bold">
                    Pilihan Menu Kami
                </h2>

                <p class="text-gray-500 mt-3 text-sm sm:text-base">
                    Pilihan terbaik dari dapur Edelweiss
                </p>

            </div>

            {{-- GRID --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8 lg:gap-10">

                @php
                    use Illuminate\Support\Facades\File;
                @endphp

                @forelse($produk->take(3) as $item)

                @php

                    $folderPath = public_path('img/menu/' . $item->gambar);

                    $firstImage = null;

                    if(File::exists($folderPath)){

                        $files = collect(File::files($folderPath))
                            ->filter(function($file){

                                return in_array(
                                    strtolower($file->getExtension()),
                                    ['jpg','jpeg','png','webp']
                                );

                            })->sortBy(function($file){

                                return $file->getFilename();

                            });

                        if($files->count()){
                            $firstImage = $files->first()->getFilename();
                        }
                    }

                @endphp

                {{-- CARD --}}
                <div class="group relative">

                    {{-- GLOW --}}
                    <div class="absolute inset-0
                                bg-[#3e2723]/10
                                blur-2xl
                                opacity-0
                                group-hover:opacity-100
                                transition duration-500">
                    </div>

                    {{-- CARD --}}
                    <div class="relative
                                overflow-hidden
                                rounded-[2rem]
                                backdrop-blur-2xl
                                bg-white/40
                                border border-white/30
                                shadow-2xl
                                hover:-translate-y-2
                                transition duration-500">

                        {{-- SHINE --}}
                        <div class="absolute inset-0
                                    bg-gradient-to-br
                                    from-white/30
                                    via-transparent
                                    to-transparent
                                    opacity-0
                                    group-hover:opacity-100
                                    transition duration-700">
                        </div>

                        {{-- IMAGE --}}
                        <div class="relative overflow-hidden
                                    h-[240px] sm:h-[260px] md:h-[280px]">

                            @if($firstImage)

                            <img
                                src="{{ asset('img/menu/'.$item->gambar.'/'.$firstImage) }}"
                                class="w-full h-full object-cover
                                       group-hover:scale-110
                                       transition duration-700"
                            >

                            @else

                            <div class="w-full h-full
                                        flex items-center justify-center
                                        text-gray-400">

                                Tidak ada gambar

                            </div>

                            @endif

                        </div>

                        {{-- CONTENT --}}
                        <div class="relative p-5 sm:p-6 text-center">

                            {{-- NAMA --}}
                            <h3 class="text-xl sm:text-2xl
                                       font-bold
                                       text-[#3e2723]
                                       drop-shadow-md
                                       group-hover:text-[#2c1b18]
                                       transition">

                                {{ $item->nama_produk }}

                            </h3>

                            {{-- HARGA --}}
                            <div class="mt-4">

                                <span class="text-lg sm:text-xl
                                             font-black
                                             bg-gradient-to-r
                                             from-[#c8a97e]
                                             via-[#f3d19c]
                                             to-[#b8860b]
                                             bg-clip-text
                                             text-transparent
                                             drop-shadow-lg">

                                    Rp {{ number_format($item->harga, 0, ',', '.') }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

                @empty

                <div class="col-span-full text-center text-gray-500">

                    Belum ada produk tersedia

                </div>

                @endforelse

            </div>

        </div>

    </section>

    {{-- FOOTER --}}
    @include('layouts.footer')

</body>
</html>