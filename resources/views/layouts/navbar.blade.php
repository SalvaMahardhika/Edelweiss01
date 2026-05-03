<header id="navbar" class="fixed top-0 w-full z-50 backdrop-blur-3xl bg-white/60 border-b border-white/10 shadow-lg">

    {{-- Ellipse shadow --}}
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute left-1/2 -translate-x-1/2 top-0 w-[700px] h-[200px] bg-black/30 blur-3xl opacity-70"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-6 flex items-center justify-between py-4">

        {{-- Logo --}}
        <div class="flex items-center">
            <img src="{{ asset('img/logo/logo1.png') }}" class="h-12 drop-shadow-lg">
        </div>

        {{-- Menu --}}
        <nav class="flex gap-6 font-semibold text-[#3e2723]">

            <a href="{{ route('home') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition duration-300">
                Home
            </a>

            <a href="{{ route('about') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition duration-300">
                About
            </a>

            <a href="{{ route('menu') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition duration-300">
                Menu
            </a>

            <a href="{{ route('galeri') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition duration-300">
                Galeri
            </a>

            <a href="{{ route('kontak') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition duration-300">
                Contact
            </a>

        </nav>

    </div>
</header>