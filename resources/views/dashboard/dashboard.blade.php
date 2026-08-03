<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-[#fafafa] text-[#3e2723] overflow-x-hidden">

    @include('layouts.navbar')

    {{-- HERO SECTION --}}
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('img/dashboard/assets/3.webp') }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/45"></div>
        </div>

        {{-- GLOW EFFECTS --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/3 left-1/4 w-80 h-80 bg-[#d4af37] rounded-full blur-3xl opacity-20"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-[#3e2723] rounded-full blur-3xl opacity-20"></div>
        </div>

        <div class="relative z-10 w-full max-w-5xl mx-auto px-6 text-center">
            <div class="backdrop-blur-2xl bg-white/10 border border-white/20 rounded-[2.5rem] p-10 md:p-14 shadow-2xl">
                <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
                    Kelezatan yang<br class="hidden sm:block"> Tumbuh dari Keindahan
                </h1>
                <p class="text-white/80 text-base md:text-lg max-w-2xl mx-auto mb-8">
                    Roti rumahan yang terinspirasi dari keindahan bunga Edelweiss.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('menu') }}" class="px-8 py-4 rounded-2xl bg-[#3e2723] text-white font-semibold shadow-xl hover:bg-[#2c1b18] hover:scale-105 transition duration-300">Lihat Menu</a>
                    <a href="{{ route('kontak') }}" class="px-8 py-4 rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 text-white font-semibold hover:bg-white/20 hover:scale-105 transition duration-300">Kontak</a>
                </div>
            </div>
        </div>
    </section>

    {{-- PRODUK SECTION --}}
    <section class="relative py-24 bg-gradient-to-br from-[#fafafa] via-[#f5efe8] to-[#ede5dc]">
        <div class="absolute top-20 left-1/3 w-72 h-72 bg-[#d4af37]/20 blur-3xl rounded-full"></div>
        <div class="absolute bottom-10 right-1/4 w-72 h-72 bg-[#3e2723]/20 blur-3xl rounded-full"></div>

        <div class="relative max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold">Pilihan Menu Kami</h2>
                <p class="text-gray-500 mt-3">Pilihan terbaik dari dapur Edelweiss</p>
            </div>

            {{-- GRID 4 PRODUK --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @php use Illuminate\Support\Facades\File; @endphp

                @forelse($produk as $item)
                    @php
                        $folderPath = public_path('img/menu/' . $item->gambar);
                        $firstImage = null;
                        if(File::exists($folderPath)){
                            $files = collect(File::files($folderPath))->filter(fn($f) => in_array(strtolower($f->getExtension()), ['jpg','jpeg','png','webp']));
                            if($files->count()) $firstImage = $files->first()->getFilename();
                        }
                    @endphp

                    <div class="group relative overflow-hidden rounded-[2rem] bg-white/40 border border-white/30 shadow-2xl hover:-translate-y-2 transition duration-500">
                        <div class="h-64 overflow-hidden">
                            @if($firstImage)
                                <img src="{{ asset('img/menu/'.$item->gambar.'/'.$firstImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">Tidak ada gambar</div>
                            @endif
                        </div>
                        <div class="p-6 text-center">
                            <h3 class="text-xl font-bold text-[#3e2723]">{{ $item->nama_produk }}</h3>
                            <div class="mt-4">
                                <span class="text-lg font-black text-transparent bg-clip-text bg-gradient-to-r from-[#c8a97e] to-[#b8860b]">
                                    Rp {{ number_format($item->harga, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500">Belum ada produk tersedia</div>
                @endforelse
            </div>
        </div>
    </section>

    @include('layouts.footer')
</body>
</html>