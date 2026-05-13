<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Edelweiss Bakery</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723] overflow-x-hidden">

@include('layouts.navbar')

<main class="relative overflow-hidden pt-28 sm:pt-32">

    {{-- BACKGROUND GLOW --}}
    <div class="absolute inset-0 -z-10 overflow-hidden">

        <div class="absolute top-32 left-1/4
                    w-60 sm:w-80 md:w-96
                    h-60 sm:h-80 md:h-96
                    bg-[#c8a97e]/30
                    blur-3xl
                    rounded-full">
        </div>

        <div class="absolute bottom-32 right-1/4
                    w-60 sm:w-80 md:w-96
                    h-60 sm:h-80 md:h-96
                    bg-[#3e2723]/25
                    blur-3xl
                    rounded-full">
        </div>

        <div class="absolute top-1/2 left-1/2 -translate-x-1/2
                    w-60 sm:w-80 md:w-96
                    h-60 sm:h-80 md:h-96
                    bg-[#f3e8dc]/40
                    blur-3xl
                    rounded-full">
        </div>

    </div>

    {{-- ================= TITLE ================= --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 text-center mb-12 sm:mb-16">

        <h1 class="text-3xl sm:text-4xl md:text-5xl
                   font-black
                   mb-4
                   leading-tight">

            Hubungi Kami

        </h1>

        <p class="text-[#6b4f4f]
                  text-sm sm:text-base md:text-lg
                  max-w-2xl mx-auto">

            Kami siap melayani dan menjawab
            pertanyaan Anda

        </p>

    </section>

    {{-- ================= CONTENT ================= --}}
    <section class="max-w-7xl mx-auto
                    px-4 sm:px-6
                    pb-20 sm:pb-28
                    grid grid-cols-1 lg:grid-cols-2
                    gap-8 lg:gap-10">

        {{-- ================= INFO KONTAK ================= --}}
        <div class="relative
                    backdrop-blur-3xl
                    bg-white/30
                    border border-white/30
                    rounded-[2rem]
                    sm:rounded-[2.5rem]
                    p-6 sm:p-8
                    shadow-2xl
                    overflow-hidden">

            {{-- INNER GLOW --}}
            <div class="absolute inset-0
                        rounded-[2rem]
                        sm:rounded-[2.5rem]
                        bg-white/20
                        blur-2xl
                        opacity-30">
            </div>

            <div class="relative z-10 space-y-8">

                {{-- TITLE --}}
                <div>

                    <h2 class="text-2xl sm:text-3xl
                               font-bold
                               mb-2">

                        Informasi Kontak

                    </h2>

                    <p class="text-[#6b4f4f] text-sm sm:text-base">
                        Silakan hubungi kami melalui platform berikut
                    </p>

                </div>

                {{-- INSTAGRAM --}}
                <div class="flex flex-col sm:flex-row
                            sm:items-center
                            sm:justify-between
                            gap-4
                            rounded-2xl
                            bg-white/20
                            border border-white/30
                            p-5">

                    <div>

                        <p class="font-bold text-lg">
                            Instagram
                        </p>

                        <p class="text-[#6b4f4f] text-sm sm:text-base">
                            @edelweissbakery
                        </p>

                    </div>

                    <a href="https://instagram.com/edelweissbakery"
                       target="_blank"
                       class="w-full sm:w-auto
                              text-center
                              px-5 py-3
                              rounded-xl
                              bg-[#3e2723]
                              text-white
                              font-semibold
                              hover:bg-[#2c1b18]
                              transition
                              shadow-lg">

                        Kunjungi

                    </a>

                </div>

                {{-- WHATSAPP 1 --}}
                <div class="flex flex-col sm:flex-row
                            sm:items-center
                            sm:justify-between
                            gap-4
                            rounded-2xl
                            bg-white/20
                            border border-white/30
                            p-5">

                    <div>

                        <p class="font-bold text-lg">
                            WhatsApp 1
                        </p>

                        <p class="text-[#6b4f4f] text-sm sm:text-base">
                            08xxxxxxxxxx
                        </p>

                    </div>

                    <a href="https://wa.me/628xxxxxxxxxx"
                       target="_blank"
                       class="w-full sm:w-auto
                              text-center
                              px-5 py-3
                              rounded-xl
                              bg-[#c8a97e]
                              text-white
                              font-semibold
                              hover:opacity-90
                              transition
                              shadow-lg">

                        Chat

                    </a>

                </div>

                {{-- WHATSAPP 2 --}}
                <div class="flex flex-col sm:flex-row
                            sm:items-center
                            sm:justify-between
                            gap-4
                            rounded-2xl
                            bg-white/20
                            border border-white/30
                            p-5">

                    <div>

                        <p class="font-bold text-lg">
                            WhatsApp 2
                        </p>

                        <p class="text-[#6b4f4f] text-sm sm:text-base">
                            08xxxxxxxxxx
                        </p>

                    </div>

                    <a href="https://wa.me/628xxxxxxxxxx"
                       target="_blank"
                       class="w-full sm:w-auto
                              text-center
                              px-5 py-3
                              rounded-xl
                              bg-[#c8a97e]
                              text-white
                              font-semibold
                              hover:opacity-90
                              transition
                              shadow-lg">

                        Chat

                    </a>

                </div>

                {{-- ALAMAT --}}
                <div class="rounded-2xl
                            bg-white/20
                            border border-white/30
                            p-5">

                    <p class="font-bold text-lg mb-3">
                        Alamat
                    </p>

                    <p class="text-[#6b4f4f]
                              leading-relaxed
                              text-sm sm:text-base
                              mb-5">

                        7P56+CFV, Jl. Dusun Beji Gg. Geneng,
                        RT.1/RW.1, Bejigeneng, Sumbersuko,
                        Kec. Purwosari, Pasuruan,
                        Jawa Timur 67162

                    </p>

                    <a href="https://maps.app.goo.gl/XFfSpJPetJadrVYW7"
                       target="_blank"
                       class="inline-flex items-center justify-center
                              w-full sm:w-auto
                              px-5 py-3
                              rounded-xl
                              bg-[#3e2723]
                              text-white
                              font-semibold
                              hover:bg-[#2c1b18]
                              transition
                              shadow-lg">

                        Lihat di Maps

                    </a>

                </div>

            </div>

        </div>

        {{-- ================= GOOGLE MAPS ================= --}}
        <div class="relative
                    backdrop-blur-3xl
                    bg-white/30
                    border border-white/30
                    rounded-[2rem]
                    sm:rounded-[2.5rem]
                    p-3 sm:p-4
                    shadow-2xl
                    overflow-hidden">

            {{-- GLOW --}}
            <div class="absolute inset-0
                        bg-white/20
                        blur-2xl
                        opacity-30
                        rounded-[2rem]
                        sm:rounded-[2.5rem]">
            </div>

            <div class="relative z-10">

                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3757.8507702074066!2d112.7120584933753!3d-7.74040884181116!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7d6b24eb936c5%3A0x70b4778b5f95a563!2sEdelweiss%20Brownies!5e1!3m2!1sid!2sid!4v1777745479330!5m2!1sid!2sid"
                    class="w-full
                           h-[320px] sm:h-[450px] lg:h-[100%]
                           min-h-[320px]
                           rounded-[1.5rem]
                           border-0"
                    loading="lazy">
                </iframe>

            </div>

        </div>

    </section>

</main>

@include('layouts.footer')

</body>
</html>