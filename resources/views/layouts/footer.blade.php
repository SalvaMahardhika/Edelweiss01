<footer class="relative overflow-hidden bg-[#3e2723] text-white">

    {{-- GLOW BACKGROUND --}}
    <div class="absolute inset-0 pointer-events-none">

        <div class="absolute -top-10 left-1/4
                    w-72 h-72
                    bg-[#d4af37]/10
                    blur-3xl rounded-full">
        </div>

        <div class="absolute bottom-0 right-1/4
                    w-72 h-72
                    bg-white/5
                    blur-3xl rounded-full">
        </div>

    </div>

    {{-- CONTENT --}}
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-8">

        <div class="flex flex-col lg:flex-row
                    items-center
                    justify-between
                    gap-6">

            {{-- LEFT --}}
            <div class="text-center lg:text-left">

                <h3 class="text-xl font-bold mb-2">
                    Edelweiss Bakery
                </h3>

                <p class="text-sm text-white/70 leading-relaxed max-w-md">

                    Roti rumahan dengan cita rasa hangat
                    dan sentuhan premium khas Edelweiss.

                </p>

            </div>

            {{-- CENTER MENU --}}
            <div class="flex flex-wrap justify-center gap-3 sm:gap-5 text-sm font-medium">

                <a href="{{ route('home') }}"
                   class="px-4 py-2 rounded-xl
                          hover:bg-white/10
                          hover:text-[#d4af37]
                          transition duration-300">

                    Home

                </a>

                <a href="{{ route('menu') }}"
                   class="px-4 py-2 rounded-xl
                          hover:bg-white/10
                          hover:text-[#d4af37]
                          transition duration-300">

                    Menu

                </a>

                <a href="{{ route('about') }}"
                   class="px-4 py-2 rounded-xl
                          hover:bg-white/10
                          hover:text-[#d4af37]
                          transition duration-300">

                    About

                </a>

                <a href="{{ route('galeri') }}"
                   class="px-4 py-2 rounded-xl
                          hover:bg-white/10
                          hover:text-[#d4af37]
                          transition duration-300">

                    Galeri

                </a>

                <a href="{{ route('kontak') }}"
                   class="px-4 py-2 rounded-xl
                          hover:bg-white/10
                          hover:text-[#d4af37]
                          transition duration-300">

                    Contact

                </a>

            </div>

            {{-- RIGHT --}}
            <div class="text-center lg:text-right">

                <p class="text-sm text-white/60">

                    &copy; {{ date('Y') }}
                    Edelweiss Bakery

                </p>

                <p class="text-xs text-white/40 mt-1">

                    All rights reserved.

                </p>

            </div>

        </div>

    </div>

</footer>