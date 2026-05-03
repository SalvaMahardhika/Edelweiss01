<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edelweiss Bakery</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Icon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-[#fafafa] text-[#3e2723]">

    {{-- Navbar --}}
    @include('layouts.navbar')

    {{-- ================= HERO ================= --}}
    <section class="relative h-screen flex items-center justify-center overflow-hidden">

        {{-- Background --}}
        <div class="absolute inset-0">
            <img src="{{ asset('img/dashboard/assets/1.png') }}" 
                 class="w-full h-full object-cover">

            <div class="absolute inset-0 bg-black/40"></div>
        </div>

        {{-- Glow --}}
        <div class="absolute inset-0">
            <div class="absolute top-1/3 left-1/4 w-80 h-80 bg-[#d4af37] rounded-full blur-3xl opacity-20"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-[#3e2723] rounded-full blur-3xl opacity-20"></div>
        </div>

        {{-- Glass --}}
        <div class="relative max-w-4xl mx-auto px-6">
            <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-3xl p-10 md:p-12 text-center shadow-2xl">

                <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Kelezatan yang Tumbuh dari Keindahan
                </h1>

                <p class="text-white/80 text-lg mb-8 max-w-xl mx-auto">
                    Roti rumahan yang terinspirasi dari keindahan bunga Edelweiss.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">

                    <a href="{{ route('menu') }}" 
                       class="px-8 py-4 bg-[#3e2723] text-white font-semibold rounded-xl hover:bg-[#2c1b18] transition shadow-lg">
                        Lihat Menu
                    </a>

                    <a href="#" 
                       class="px-8 py-4 bg-white/10 backdrop-blur text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition">
                        Kontak
                    </a>

                </div>

            </div>
        </div>

    </section>

    {{-- ================= PRODUK LIQUID GLASS PREMIUM ================= --}}
<section class="relative py-24 overflow-hidden">

    {{-- Background gradient + glow --}}
    <div class="absolute inset-0 bg-gradient-to-br from-[#fafafa] via-[#f5efe8] to-[#ede5dc]"></div>

    <div class="absolute inset-0">
        <div class="absolute top-20 left-1/3 w-[300px] h-[300px] bg-[#d4af37]/20 blur-3xl rounded-full"></div>
        <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-[#3e2723]/20 blur-3xl rounded-full"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-6">

        {{-- Title --}}
        <div class="text-center mb-16">
            <i class="fas fa-bread-slice mb-3 text-xl text-[#3e2723]/70"></i>
            <h2 class="text-3xl font-semibold">Pilihan menu kami</h2>
            <p class="text-gray-500 mt-2">Pilihan terbaik dari dapur Edelweiss</p>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

            @forelse($produk as $item)

            {{-- CARD --}}
            <div class="group relative rounded-3xl overflow-hidden">

                {{-- glow belakang card --}}
                <div class="absolute inset-0 bg-[#3e2723]/10 blur-2xl opacity-0 group-hover:opacity-100 transition"></div>

                {{-- glass card --}}
                <div class="relative backdrop-blur-2xl bg-white/50 border border-white/40 rounded-3xl shadow-xl overflow-hidden transform transition duration-300 group-hover:-translate-y-2 group-hover:shadow-2xl">

                    {{-- IMAGE --}}
                    <div class="h-56 overflow-hidden">
                        <img src="{{ asset('storage/' . $item->gambar) }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>

                    {{-- CONTENT --}}
                    <div class="p-6 text-center">

                        <h3 class="font-semibold text-lg mb-2">
                            {{ $item->nama_produk }}
                        </h3>

                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            {{ $item->deskripsi }}
                        </p>

                        {{-- harga --}}
                        <div class="mb-5">
                            <span class="text-[#d4af37] font-bold text-lg">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- button --}}
                        <a href="{{ route('menu') }}"
                           class="inline-block px-5 py-2 rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-md">
                            Lihat
                        </a>

                    </div>

                </div>

            </div>

            @empty
                <p class="col-span-3 text-center text-gray-500">
                    Belum ada produk tersedia
                </p>
            @endforelse

        </div>

    </div>

</section>

    {{-- ================= FOOTER ================= --}}
    @include('layouts.footer')

</body>
</html>