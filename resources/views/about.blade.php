<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Edelweiss Bakery</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f3ede7] text-[#3e2723]">

    {{-- Navbar --}}
    @include('layouts.navbar')

    <main class="relative overflow-hidden">

        {{-- Glow Background (lebih warm) --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-40 left-1/4 w-96 h-96 bg-[#d4af37] blur-3xl opacity-20"></div>
            <div class="absolute bottom-40 right-1/4 w-96 h-96 bg-[#3e2723] blur-3xl opacity-20"></div>
        </div>

        {{-- ================= HERO ================= --}}
        <section class="max-w-4xl mx-auto px-6 py-24 text-center">
            <div class="backdrop-blur-xl bg-[#ffffff]/40 border border-[#3e2723]/20 rounded-3xl p-10 shadow-xl">

                <h1 class="text-4xl md:text-5xl font-bold mb-6 text-[#3e2723]">
                    Cerita Edelweiss
                </h1>

                <p class="text-[#5c4438] text-lg max-w-2xl mx-auto">
                    Roti rumahan yang terinspirasi dari keindahan sederhana bunga Edelweiss.
                </p>

            </div>
        </section>

        {{-- ================= STORY ================= --}}
        <section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-12 items-center">

            {{-- Image Glass --}}
            <div class="backdrop-blur-xl bg-[#ffffff]/30 border border-[#3e2723]/20 rounded-2xl p-3 shadow-lg">
                <div class="w-full h-[350px] bg-[#e7dfd7] rounded-xl"></div>
            </div>

            {{-- Text --}}
            <div>
                <h2 class="text-3xl font-semibold mb-4 text-[#3e2723]">
                    Dari Dapur Kecil
                </h2>

                <p class="text-[#6b4f43] leading-relaxed">
                    Edelweiss Bakery berawal dari dapur sederhana dengan satu tujuan:
                    menghadirkan roti yang hangat dan bisa dinikmati siapa saja.
                    <br><br>
                    Kami percaya, sesuatu yang sederhana justru bisa jadi yang paling berkesan.
                </p>
            </div>

        </section>

        {{-- ================= VALUES ================= --}}
        <section class="py-20">
            <div class="max-w-6xl mx-auto px-6">

                <div class="text-center mb-12">
                    <h2 class="text-3xl font-semibold text-[#3e2723]">
                        Cara Kami Membuat
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-8">

                    {{-- Card --}}
                    <div class="backdrop-blur-xl bg-[#ffffff]/40 border border-[#3e2723]/20 p-6 rounded-2xl text-center shadow-md hover:scale-105 transition">
                        <h3 class="font-semibold text-xl mb-2 text-[#3e2723]">Pelan</h3>
                        <p class="text-[#6b4f43] text-sm">Adonan diberi waktu, bukan dipaksa.</p>
                    </div>

                    <div class="backdrop-blur-xl bg-[#ffffff]/40 border border-[#3e2723]/20 p-6 rounded-2xl text-center shadow-md hover:scale-105 transition">
                        <h3 class="font-semibold text-xl mb-2 text-[#3e2723]">Sederhana</h3>
                        <p class="text-[#6b4f43] text-sm">Bahan sedikit, rasa tetap kena.</p>
                    </div>

                    <div class="backdrop-blur-xl bg-[#ffffff]/40 border border-[#3e2723]/20 p-6 rounded-2xl text-center shadow-md hover:scale-105 transition">
                        <h3 class="font-semibold text-xl mb-2 text-[#3e2723]">Konsisten</h3>
                        <p class="text-[#6b4f43] text-sm">Selalu enak, setiap waktu.</p>
                    </div>

                </div>

            </div>
        </section>

        {{-- ================= CTA ================= --}}
        <section class="max-w-4xl mx-auto px-6 pb-24">

            <div class="backdrop-blur-xl bg-[#3e2723]/90 border border-[#3e2723]/40 rounded-3xl p-10 text-center shadow-xl text-white">

                <h2 class="text-3xl font-semibold mb-4">
                    Datang dan Rasakan Sendiri
                </h2>

                <p class="text-[#f3ede7] mb-8">
                    Kami percaya pengalaman terbaik adalah yang langsung dirasakan.
                </p>

                <a href="#" 
                   class="inline-block bg-[#d4af37] text-[#3e2723] px-6 py-3 rounded-xl font-semibold hover:opacity-90 transition">
                    Lihat Menu
                </a>

            </div>

        </section>

    </main>

    {{-- Footer --}}
    @include('layouts.footer')

</body>
</html>