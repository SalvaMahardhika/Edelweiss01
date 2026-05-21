<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f3ede7] text-[#3e2723] overflow-x-hidden">

    {{-- NAVBAR --}}
    @include('layouts.navbar')

    <main class="relative overflow-hidden">

        {{-- GLOW BACKGROUND --}}
        <div class="absolute inset-0 -z-10 overflow-hidden">

            <div class="absolute top-40 left-1/4
                        w-60 sm:w-80 md:w-96
                        h-60 sm:h-80 md:h-96
                        bg-[#d4af37]
                        blur-3xl
                        opacity-20">
            </div>

            <div class="absolute bottom-40 right-1/4
                        w-60 sm:w-80 md:w-96
                        h-60 sm:h-80 md:h-96
                        bg-[#3e2723]
                        blur-3xl
                        opacity-20">
            </div>

        </div>

        {{-- ================= HERO ================= --}}
        <section class="max-w-5xl mx-auto px-4 sm:px-6 pt-28 pb-16 sm:pt-32 sm:pb-20">

            <div class="backdrop-blur-2xl
                        bg-[#ffffff]/40
                        border border-[#3e2723]/20
                        rounded-[2rem]
                        sm:rounded-[2.5rem]
                        p-6 sm:p-10 md:p-14
                        shadow-2xl
                        text-center">

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl
                           font-black
                           mb-6
                           leading-tight
                           text-[#3e2723]">

                    Cerita Edelweiss

                </h1>

                <p class="text-[#5c4438]
                          text-sm sm:text-base md:text-lg
                          leading-relaxed
                          max-w-2xl
                          mx-auto">

                    Roti rumahan yang terinspirasi dari
                    keindahan sederhana bunga Edelweiss.

                </p>

            </div>

        </section>

        {{-- ================= STORY ================= --}}
        <section class="max-w-7xl mx-auto
                        px-4 sm:px-6
                        py-10 sm:py-16
                        grid grid-cols-1 lg:grid-cols-2
                        gap-10 lg:gap-16
                        items-center">

            {{-- IMAGE --}}
            <div class="order-1">

                <div class="backdrop-blur-2xl
                            bg-[#ffffff]/30
                            border border-[#3e2723]/20
                            rounded-[2rem]
                            p-3 sm:p-4
                            shadow-2xl">

                    <div class="relative overflow-hidden rounded-[1.5rem]">

                        <img src="{{ asset('img/dashboard/assets/3.png') }}"
                             class="w-full
                                    h-[250px] sm:h-[350px] md:h-[450px]
                                    object-cover">

                        <div class="absolute inset-0
                                    bg-gradient-to-t
                                    from-black/20
                                    to-transparent">
                        </div>

                    </div>

                </div>

            </div>

            {{-- TEXT --}}
            <div class="order-2">

                <h2 class="text-3xl sm:text-4xl
                           font-bold
                           mb-5
                           text-[#3e2723]">

                    Dari Dapur Kecil

                </h2>

                <p class="text-[#6b4f43]
                          leading-relaxed
                          text-sm sm:text-base md:text-lg">

                    Edelweiss Bakery berawal dari dapur sederhana
                    dengan satu tujuan:
                    menghadirkan roti yang hangat dan bisa
                    dinikmati siapa saja.

                    <br><br>

                    Kami percaya, sesuatu yang sederhana justru
                    bisa jadi yang paling berkesan.

                </p>

            </div>

        </section>

        {{-- ================= VALUES ================= --}}
        <section class="py-16 sm:py-20">

            <div class="max-w-7xl mx-auto px-4 sm:px-6">

                {{-- TITLE --}}
                <div class="text-center mb-12">

                    <h2 class="text-3xl sm:text-4xl font-bold text-[#3e2723]">
                        Cara Kami Membuat
                    </h2>

                </div>

                {{-- GRID --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 sm:gap-8">

                    {{-- CARD 1 --}}
                    <div class="group
                                backdrop-blur-2xl
                                bg-[#ffffff]/40
                                border border-[#3e2723]/20
                                p-6 sm:p-8
                                rounded-[2rem]
                                text-center
                                shadow-xl
                                hover:-translate-y-2
                                transition duration-500">

                        <div class="w-16 h-16 mx-auto mb-5
                                    rounded-full
                                    bg-[#d4af37]/20
                                    flex items-center justify-center
                                    text-2xl">

                            🍞

                        </div>

                        <h3 class="font-bold text-2xl mb-3 text-[#3e2723]">
                            Pelan
                        </h3>

                        <p class="text-[#6b4f43] text-sm sm:text-base leading-relaxed">
                            Adonan diberi waktu,
                            bukan dipaksa.
                        </p>

                    </div>

                    {{-- CARD 2 --}}
                    <div class="group
                                backdrop-blur-2xl
                                bg-[#ffffff]/40
                                border border-[#3e2723]/20
                                p-6 sm:p-8
                                rounded-[2rem]
                                text-center
                                shadow-xl
                                hover:-translate-y-2
                                transition duration-500">

                        <div class="w-16 h-16 mx-auto mb-5
                                    rounded-full
                                    bg-[#d4af37]/20
                                    flex items-center justify-center
                                    text-2xl">

                            ✨

                        </div>

                        <h3 class="font-bold text-2xl mb-3 text-[#3e2723]">
                            Sederhana
                        </h3>

                        <p class="text-[#6b4f43] text-sm sm:text-base leading-relaxed">
                            Bahan sedikit,
                            rasa tetap kena.
                        </p>

                    </div>

                    {{-- CARD 3 --}}
                    <div class="group
                                backdrop-blur-2xl
                                bg-[#ffffff]/40
                                border border-[#3e2723]/20
                                p-6 sm:p-8
                                rounded-[2rem]
                                text-center
                                shadow-xl
                                hover:-translate-y-2
                                transition duration-500">

                        <div class="w-16 h-16 mx-auto mb-5
                                    rounded-full
                                    bg-[#d4af37]/20
                                    flex items-center justify-center
                                    text-2xl">

                            🥐

                        </div>

                        <h3 class="font-bold text-2xl mb-3 text-[#3e2723]">
                            Konsisten
                        </h3>

                        <p class="text-[#6b4f43] text-sm sm:text-base leading-relaxed">
                            Selalu enak,
                            setiap waktu.
                        </p>

                    </div>

                </div>

            </div>

        </section>

        {{-- ================= CTA ================= --}}
        <section class="max-w-5xl mx-auto px-4 sm:px-6 pb-20 sm:pb-28">

            <div class="relative overflow-hidden
                        backdrop-blur-2xl
                        bg-[#3e2723]/90
                        border border-[#3e2723]/40
                        rounded-[2rem]
                        sm:rounded-[2.5rem]
                        p-8 sm:p-12
                        text-center
                        shadow-2xl
                        text-white">

                {{-- GLOW --}}
                <div class="absolute inset-0 overflow-hidden">

                    <div class="absolute -top-20 -left-20
                                w-60 h-60
                                bg-[#d4af37]/20
                                blur-3xl
                                rounded-full">
                    </div>

                    <div class="absolute -bottom-20 -right-20
                                w-60 h-60
                                bg-white/10
                                blur-3xl
                                rounded-full">
                    </div>

                </div>

                <div class="relative z-10">

                    <h2 class="text-3xl sm:text-4xl
                               font-bold
                               mb-5">

                        Datang dan Rasakan Sendiri

                    </h2>

                    <p class="text-[#f3ede7]
                              text-sm sm:text-base md:text-lg
                              mb-8
                              max-w-2xl
                              mx-auto">

                        Kami percaya pengalaman terbaik
                        adalah yang langsung dirasakan.

                    </p>

                    <a href="{{ route('menu') }}"
                       class="inline-flex items-center justify-center
                              w-full sm:w-auto
                              px-8 py-4
                              rounded-2xl
                              bg-[#d4af37]
                              text-[#3e2723]
                              font-bold
                              shadow-xl
                              hover:scale-105
                              hover:opacity-90
                              transition duration-300">

                        Lihat Menu

                    </a>

                </div>

            </div>

        </section>

    </main>

    {{-- FOOTER --}}
    @include('layouts.footer')

</body>
</html>