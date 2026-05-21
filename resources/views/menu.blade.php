<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }

        /* ✨ TEXT GLOW */
        .glow-hover {
            transition: all 0.3s ease;
        }

        .group:hover .glow-hover {
            text-shadow: 0 0 12px rgba(255,255,255,0.6),
                         0 0 20px rgba(200,169,126,0.6);
        }

        /* 🟡 GOLD GRADIENT TEXT */
        .gold-text {
            background: linear-gradient(135deg, #e6c89c, #c8a97e, #a67c52);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* 💎 GLASS SHINE */
        .glass-shine::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                120deg,
                transparent 30%,
                rgba(255,255,255,0.4),
                transparent 70%
            );
            transform: rotate(25deg);
            transition: all 0.6s ease;
        }

        .group:hover .glass-shine::before {
            top: 100%;
            left: 100%;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] text-[#3e2723]">

@include('layouts.navbar')

<main class="relative overflow-hidden pt-32">

    {{-- BACKGROUND GLOW --}}
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-40 left-1/3 w-96 h-96 bg-[#c8a97e]/20 blur-3xl rounded-full"></div>
        <div class="absolute bottom-40 right-1/3 w-96 h-96 bg-[#3e2723]/20 blur-3xl rounded-full"></div>
    </div>

    {{-- TITLE --}}
    <section class="max-w-6xl mx-auto px-6 text-center mb-14">
        <h1 class="text-4xl md:text-5xl font-semibold tracking-wide glow-hover">
            Pilihan Menu Kami
        </h1>
        <p class="text-[#6b4f4f] mt-2">
            Fresh & handmade dengan kualitas terbaik
        </p>
    </section>

    {{-- BUTTON TAMBAH --}}
    @auth
    @if(in_array(auth()->user()->role, ['admin','super_admin']))
    <div class="text-center mb-12">
        <button onclick="openModal()" 
            class="px-8 py-3 rounded-2xl bg-[#3e2723] text-white shadow-xl hover:scale-105 transition">
            + Tambah Produk
        </button>
    </div>
    @endif
    @endauth

    {{-- GRID --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-20 sm:pb-24">

    <div class="grid 
                grid-cols-2 
                md:grid-cols-2 
                xl:grid-cols-4 
                gap-4 sm:gap-6 lg:gap-8">

        @foreach($produk as $item)

        @php
            $folder = public_path('img/menu/' . $item->gambar);

            $files = file_exists($folder) ? scandir($folder) : [];

            $images = array_values(array_diff($files, ['.', '..']));
        @endphp

        {{-- CARD --}}
        <div class="relative group glass-shine
                    {{ !$item->status ? 'opacity-60 grayscale-[20%]' : '' }}
                    backdrop-blur-2xl
                    bg-white/30
                    border border-white/40
                    rounded-2xl sm:rounded-3xl
                    overflow-hidden
                    shadow-[0_10px_40px_rgba(0,0,0,0.15)]
                    transition duration-500
                    hover:scale-[1.03]">

            {{-- DELETE & TOGGLE STATUS --}}
            @auth
            @if(in_array(auth()->user()->role, ['admin','super_admin']))

            {{-- DELETE BUTTON --}}
            <form method="POST"
                  action="{{ route('produk.destroy', $item->id_produk) }}"
                  class="absolute top-2 right-2 sm:top-4 sm:right-4 z-30">

                @csrf
                @method('DELETE')

                <button
                    class="w-7 h-7 sm:w-8 sm:h-8
                           rounded-full
                           bg-red-500/90
                           text-white
                           text-xs sm:text-sm
                           shadow-lg
                           hover:scale-110
                           transition">
                    ✕
                </button>
            </form>

            {{-- TOGGLE STATUS --}}
            <form method="POST"
                  action="{{ route('produk.update', $item->id_produk) }}"
                  class="absolute top-2 left-2 sm:top-4 sm:left-4 z-30">

                @csrf
                @method('PUT')

                <input type="hidden" name="toggle_status" value="1">

                <button type="submit"
                    class="relative w-14 h-8 rounded-full
                           transition duration-300
                           shadow-[0_8px_24px_rgba(0,0,0,0.25)]
                           border border-white/30
                           backdrop-blur-xl
                           {{ $item->status ? 'bg-green-500/90' : 'bg-red-500/80' }}">

                    {{-- BULATAN TOGGLE --}}
                    <div class="absolute top-1 w-6 h-6
                                rounded-full
                                bg-white
                                shadow-lg
                                transition-all duration-300
                                flex items-center justify-center
                                text-[10px] font-bold
                                text-[#3e2723]
                                {{ $item->status ? 'left-7' : 'left-1' }}">
                        {{ $item->status ? 'ON' : 'OFF' }}
                    </div>
                </button>
            </form>

            @endif
            @endauth

            {{-- IMAGE --}}
            <div class="relative overflow-hidden
                        h-40 sm:h-52 md:h-56">

                @if(count($images) > 0)
                <img
                    src="{{ asset('img/menu/' . $item->gambar . '/' . $images[0]) }}"
                    class="w-full h-full object-cover
                           transition duration-500
                           group-hover:scale-110"
                >
                @endif

                {{-- OVERLAY --}}
                <div class="absolute inset-0
                            bg-gradient-to-t
                            from-black/50
                            to-transparent">
                </div>

                {{-- DETAIL --}}
                <div class="absolute inset-0
                            flex items-center justify-center
                            opacity-0 group-hover:opacity-100
                            transition">

                    <a href="{{ route('menu.show', $item->id_produk) }}"
                       class="px-3 py-2 sm:px-5
                              rounded-xl
                              bg-white/80
                              backdrop-blur
                              text-[#3e2723]
                              text-xs sm:text-sm
                              font-medium
                              shadow
                              hover:scale-105
                              transition">
                        Detail →
                    </a>
                </div>
            </div>

            {{-- CONTENT --}}
            <div class="p-3 sm:p-5 md:p-6
                        bg-white/30
                        backdrop-blur-xl">

                {{-- STATUS BADGE --}}
                @auth
                @if(in_array(auth()->user()->role, ['admin','super_admin']))
                <div class="mb-3">
                    @if($item->status)
                    <span class="inline-flex items-center gap-1
                                 px-3 py-1 rounded-full
                                 text-[11px] sm:text-xs
                                 font-semibold
                                 bg-green-500/15
                                 text-green-700
                                 border border-green-500/20
                                 backdrop-blur-xl">
                        ● Produk Aktif
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1
                                 px-3 py-1 rounded-full
                                 text-[11px] sm:text-xs
                                 font-semibold
                                 bg-red-500/15
                                 text-red-700
                                 border border-red-500/20
                                 backdrop-blur-xl">
                        ● Produk Disembunyikan
                    </span>
                    @endif
                </div>
                @endif
                @endauth

                <div class="flex flex-col sm:flex-row
                            sm:justify-between
                            sm:items-center
                            gap-1 sm:gap-2
                            mb-2">

                    {{-- NAMA --}}
                    <h3 class="font-semibold
                               text-sm sm:text-base lg:text-lg
                               text-[#2d1f1b]
                               glow-hover
                               line-clamp-1">
                        {{ $item->nama_produk }}
                    </h3>

                    {{-- HARGA --}}
                    <span class="text-xs sm:text-sm
                                 font-semibold
                                 gold-text">
                        Rp {{ number_format($item->harga, 0, ',', '.') }}
                    </span>
                </div>

                {{-- DESKRIPSI --}}
                <p class="text-[#5c4033]
                          text-xs sm:text-sm
                          leading-relaxed
                          line-clamp-2">
                    {{ $item->deskripsi }}
                </p>
            </div>
        </div>

        @endforeach

    </div>

</section>


    {{-- ================= MODAL TAMBAH PRODUK ================= --}}
    <div id="modal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center">

        <div class="w-full max-w-lg p-6 rounded-3xl bg-white/30 backdrop-blur-2xl border border-white/40 shadow-[0_20px_60px_rgba(0,0,0,0.25)] relative overflow-hidden">

            {{-- SHINE --}}
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-20 -left-20 w-96 h-96 bg-white/20 blur-3xl rounded-full"></div>
            </div>

            <h2 class="text-xl font-semibold mb-6 text-center glow-hover">
                Tambah Produk
            </h2>

            <form method="POST" id="formProduk" enctype="multipart/form-data">
                @csrf

                {{-- INPUT --}}
                <input type="text" name="nama_produk" placeholder="Nama Produk"
                    class="w-full mb-4 px-4 py-3 rounded-xl bg-white/60 backdrop-blur border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e]/40 transition">

                <input type="number" name="harga" placeholder="Harga"
                    class="w-full mb-4 px-4 py-3 rounded-xl bg-white/60 backdrop-blur border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e]/40 transition">

                <textarea name="deskripsi" placeholder="Deskripsi"
                    class="w-full mb-4 px-4 py-3 rounded-xl bg-white/60 backdrop-blur border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e]/40 transition"></textarea>

                {{-- DROPZONE UPGRADE --}}
                <div id="dropzone"
                    class="border-2 border-dashed border-[#c8a97e]/40 rounded-2xl p-6 text-center cursor-pointer bg-white/30 backdrop-blur hover:bg-white/50 transition">

                    <p class="text-sm text-[#6b4f4f]">
                        Drag & Drop gambar atau klik
                    </p>

                    <p class="text-xs mt-2 text-[#8b6f63]">
                        (bisa upload multiple)
                    </p>

                </div>

                <input type="file" name="gambar[]" multiple hidden id="fileInput">

                {{-- PREVIEW --}}
                <div id="preview" class="flex gap-2 flex-wrap mt-4"></div>

                {{-- ACTION --}}
                <div class="flex gap-3 mt-6">

                    <button type="button" onclick="closeModal()" 
                        class="flex-1 py-3 rounded-xl bg-white/60 backdrop-blur border border-white/40 hover:bg-white/80 transition">
                        Batal
                    </button>

                    <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-[#3e2723] text-white shadow-lg hover:scale-105 transition">
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- ================= SCRIPT ================= --}}
    <script>

    function openModal(){
        const modal = document.getElementById('modal');
        const form = document.getElementById('formProduk');

        modal.classList.remove('hidden');
        form.reset();

        form.action = "{{ route('produk.store') }}";

        let methodInput = form.querySelector('input[name="_method"]');
        if(methodInput) methodInput.remove();

        document.getElementById('preview').innerHTML = '';
    }

    function closeModal(){
        document.getElementById('modal').classList.add('hidden');
    }

    document.getElementById('modal').addEventListener('click', function(e){
        if(e.target.id === 'modal'){
            closeModal();
        }
    });


    // ================= DRAG DROP + PREVIEW =================
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('preview');

    dropzone.onclick = () => fileInput.click();

    fileInput.addEventListener('change', showPreview);

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('bg-white/60');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('bg-white/60');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        fileInput.files = e.dataTransfer.files;
        dropzone.classList.remove('bg-white/60');
        showPreview();
    });

    function showPreview(){
        preview.innerHTML = '';

        Array.from(fileInput.files).forEach(file => {
            const reader = new FileReader();

            reader.onload = function(e){
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = "w-20 h-20 object-cover rounded-lg shadow";
                preview.appendChild(img);
            }

            reader.readAsDataURL(file);
        });
    }

    </script>


@include('layouts.footer')

</body>
</html>