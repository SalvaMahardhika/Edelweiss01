<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Edelweiss Bakery</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723]">

@include('layouts.navbar')

<main class="relative overflow-hidden pt-32">

    {{-- Background Glow --}}
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-32 left-1/4 w-96 h-96 bg-[#c8a97e]/30 blur-3xl rounded-full"></div>
        <div class="absolute bottom-32 right-1/4 w-96 h-96 bg-[#3e2723]/25 blur-3xl rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#f3e8dc]/40 blur-3xl rounded-full"></div>
    </div>

    {{-- TITLE --}}
    <section class="max-w-6xl mx-auto px-6 text-center mb-16">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">
            Hubungi Kami
        </h1>
        <p class="text-[#6b4f4f]">
            Kami siap melayani dan menjawab pertanyaan Anda
        </p>
    </section>

    {{-- CONTENT --}}
    <section class="max-w-6xl mx-auto px-6 pb-24 grid md:grid-cols-2 gap-10">

        {{-- INFO KONTAK --}}
        <div class="relative backdrop-blur-3xl bg-white/30 border border-white/30 rounded-3xl p-8 shadow-2xl">

            {{-- glow dalam --}}
            <div class="absolute inset-0 rounded-3xl bg-white/20 blur-xl opacity-30"></div>

            <div class="relative space-y-6">

                <h2 class="text-2xl font-semibold mb-4">Informasi Kontak</h2>

                {{-- IG --}}
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold">Instagram</p>
                        <p class="text-[#6b4f4f]">@edelweissbakery</p>
                    </div>
                    <a href="https://instagram.com/edelweissbakery" target="_blank"
                       class="px-4 py-2 rounded-lg bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-md">
                        Kunjungi
                    </a>
                </div>

                {{-- WA 1 --}}
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold">WhatsApp 1</p>
                        <p class="text-[#6b4f4f]">08xxxxxxxxxx</p>
                    </div>
                    <a href="https://wa.me/628xxxxxxxxxx" target="_blank"
                       class="px-4 py-2 rounded-lg bg-[#c8a97e] text-white hover:opacity-90 transition shadow-md">
                        Chat
                    </a>
                </div>

                {{-- WA 2 --}}
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold">WhatsApp 2</p>
                        <p class="text-[#6b4f4f]">08xxxxxxxxxx</p>
                    </div>
                    <a href="https://wa.me/628xxxxxxxxxx" target="_blank"
                       class="px-4 py-2 rounded-lg bg-[#c8a97e] text-white hover:opacity-90 transition shadow-md">
                        Chat
                    </a>
                </div>

                {{-- ALAMAT --}}
                <div>
                    <p class="font-semibold mb-2">Alamat</p>
                    <p class="text-[#6b4f4f] leading-relaxed mb-3">
                        7P56+CFV, Jl. Dusun Beji Gg. Geneng,<br>
                        RT.1/RW.1, Bejigeneng, Sumbersuko,<br>
                        Kec. Purwosari, Pasuruan,<br>
                        Jawa Timur 67162
                    </p>

                    <a href="https://maps.app.goo.gl/XFfSpJPetJadrVYW7" target="_blank"
                       class="inline-block px-4 py-2 rounded-lg bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-md">
                        Lihat di Maps
                    </a>
                </div>

            </div>

        </div>

        {{-- GOOGLE MAPS --}}
        <div class="relative backdrop-blur-3xl bg-white/30 border border-white/30 rounded-3xl p-4 shadow-2xl overflow-hidden">

            {{-- glow --}}
            <div class="absolute inset-0 bg-white/20 blur-xl opacity-30 rounded-3xl"></div>

            <div class="relative">

                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3757.8507702074066!2d112.7120584933753!3d-7.74040884181116!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7d6b24eb936c5%3A0x70b4778b5f95a563!2sEdelweiss%20Brownies!5e1!3m2!1sid!2sid!4v1777745479330!5m2!1sid!2sid"
                    class="w-full h-[400px] rounded-2xl border-0"
                    loading="lazy">
                </iframe>

                {{-- tombol overlay --}}
                <a href="https://maps.app.goo.gl/XFfSpJPetJadrVYW7" target="_blank"
                   >
                    
                </a>

            </div>

        </div>

    </section>

</main>

@include('layouts.footer')

</body>
</html>