<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Edelweiss Bakery</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723]">

{{-- Navbar --}}
@include('layouts.navbar')

<main class="relative overflow-hidden pt-32">

    {{-- Background Glow COKLAT --}}
    <div class="absolute inset-0 -z-10">

        {{-- gold --}}
        <div class="absolute top-32 left-1/4 w-96 h-96 bg-[#c8a97e]/30 blur-3xl rounded-full"></div>

        {{-- brown --}}
        <div class="absolute bottom-32 right-1/4 w-96 h-96 bg-[#3e2723]/25 blur-3xl rounded-full"></div>

        {{-- cream --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#f3e8dc]/40 blur-3xl rounded-full"></div>

    </div>

    {{-- depth tambahan --}}
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(62,39,35,0.05),transparent_70%)] -z-10"></div>

    {{-- TITLE --}}
    <section class="max-w-6xl mx-auto px-6 text-center mb-16">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">
            Pilihan Menu Kami
        </h1>
        <p class="text-[#6b4f4f]">
            Roti dan kue yang dibuat dengan sepenuh hati.
        </p>
    </section>

    {{-- GRID MENU --}}
    <section class="max-w-6xl mx-auto px-6 pb-24">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">

            @foreach($produk as $item)

            {{-- CARD --}}
            <div class="relative backdrop-blur-2xl bg-[#ffffff]/40 border border-white/40 rounded-3xl overflow-hidden shadow-xl hover:scale-[1.03] hover:shadow-2xl transition duration-300">

                {{-- glow layer --}}
                <div class="absolute inset-0 bg-white/20 opacity-20 blur-xl"></div>

                <div class="relative">

                    {{-- IMAGE --}}
                    <div class="h-56 overflow-hidden">
                        <img src="{{ asset('storage/' . $item->gambar) }}" 
                             class="w-full h-full object-cover hover:scale-110 transition duration-500">
                    </div>

                    {{-- CONTENT --}}
                    <div class="p-6">

                        {{-- NAMA + HARGA --}}
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold text-lg">
                                {{ $item->nama_produk }}
                            </h3>

                            <span class="text-sm font-semibold text-[#c8a97e]">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- DESKRIPSI --}}
                        <p class="text-[#6b4f4f] text-sm leading-relaxed">
                            {{ $item->deskripsi }}
                        </p>

                    </div>

                </div>

            </div>

            @endforeach

        </div>

    </section>

</main>

{{-- Footer --}}
@include('layouts.footer')

</body>
</html>