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

    {{-- ================= LIST ALBUM MURNI DISPLAY ================= --}}
    <section id="galeriAlbumContainer" class="max-w-7xl mx-auto px-4 sm:px-6 pb-24 md:pb-28 space-y-12 md:space-y-16">

        @foreach($galeri as $album)
        @php
            // PENGECEKAN PATH DYNAMIC UNTUK PUBLIC_HTML HOSTING DAN LOCALHOST
            $publicHtmlFolder = base_path('../public_html/img/galeri/' . $album->album);
            $localFolder = public_path('img/galeri/' . $album->album);

            if (File::exists($publicHtmlFolder)) {
                $folderPath = $publicHtmlFolder;
            } else {
                $folderPath = $localFolder;
            }

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

        {{-- CARD ALBUM --}}
        <div class="relative glass border border-white/30 rounded-[2rem] md:rounded-[2.5rem] p-4 sm:p-6 md:p-10 shadow-2xl shine">

            {{-- GLOW --}}
            <div class="absolute inset-0 rounded-[2rem] md:rounded-[2.5rem] bg-white/10 blur-2xl opacity-40"></div>

            <div class="relative z-10">

                {{-- INFO ALBUM --}}
                <div class="mb-8">
                    {{-- TITLE --}}
                    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 mb-4">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black glow-text break-words leading-tight">
                            {{ $album->judul }}
                        </h2>
                    </div>

                    {{-- DESKRIPSI (Gunakan whitespace-pre-line agar Spacing / Enter Paragraf Terjaga Presisi) --}}
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        <p class="text-[#6b4f4f] leading-relaxed text-sm sm:text-base md:text-lg break-words whitespace-pre-line flex-1">{{ $album->deskripsi }}</p>
                    </div>
                </div>

                {{-- ================= MASONRY FOTO GRID ================= --}}
                <div class="columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-3 md:gap-5">

                    @forelse($files as $file)
                    @php
                        $filename = $file->getFilename();
                    @endphp

                    <div class="mb-3 md:mb-5 break-inside-avoid">
                        <div class="group relative overflow-hidden rounded-2xl md:rounded-[2rem] glass-soft border border-white/30 shadow-xl">

                            {{-- SHINE OVERLAY --}}
                            <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-700 z-10 pointer-events-none"></div>

                            {{-- IMAGE --}}
                            <img
                                src="{{ asset('img/galeri/'.$album->album.'/'.$filename) }}"
                                class="w-full h-auto block object-cover group-hover:scale-[1.03] transition duration-700"
                                loading="lazy"
                                alt="Galeri Foto {{ $album->judul }}">

                            {{-- DARK GRADIENT OVERLAY ON HOVER --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-500 pointer-events-none"></div>

                        </div>
                    </div>

                    @empty
                    <div class="py-10 text-center col-span-full">
                        <div class="inline-block px-6 py-4 rounded-2xl glass-soft border border-white/30 text-[#6b4f4f] text-sm md:text-base">
                            Belum ada foto di album ini
                        </div>
                    </div>
                    @endforelse

                </div>

            </div>
        </div>
        @endforeach

    </section>

</main>

@include('layouts.footer')

{{-- ⚡ SCRIPT SINKRONISASI REALTIME BACKGROUND POLLING --}}
<script>
    let galeriRealtimeTimer = null;

    function syncRealtimeGaleri() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newContent = doc.getElementById('galeriAlbumContainer');
            const currentContent = document.getElementById('galeriAlbumContainer');

            if (newContent && currentContent) {
                // Lakukan pembaharuan DOM hanya jika ada perbedaan data album dari Admin
                if (newContent.innerHTML.trim() !== currentContent.innerHTML.trim()) {
                    currentContent.innerHTML = newContent.innerHTML;
                }
            }
        })
        .catch(err => console.error("Realtime Galeri Sync Error:", err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (galeriRealtimeTimer) clearInterval(galeriRealtimeTimer);
        // Lakukan background fetch setiap 3 detik ketika halaman sedang aktif
        galeriRealtimeTimer = setInterval(function() {
            if (document.visibilityState === 'visible') {
                syncRealtimeGaleri();
            }
        }, 3000);
    });
</script>

</body>
</html>