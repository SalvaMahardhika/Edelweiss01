<header id="navbar"
class="fixed top-0 w-full z-50 backdrop-blur-3xl bg-white/60 border-b border-white/10 shadow-lg">

    {{-- Background Glow --}}
    <div class="absolute inset-0 pointer-events-none -z-10 overflow-hidden">

        <div class="absolute left-1/2 -translate-x-1/2 top-0
                    w-[700px] h-[200px]
                    bg-black/20 blur-3xl opacity-60">
        </div>

    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6">

        <div class="flex items-center justify-between py-4">

            {{-- ================= LOGO ================= --}}
            <a href="{{ route('home') }}"
               class="flex items-center shrink-0">

                <img src="{{ asset('img/logo/logo1.png') }}"
                     class="h-10 sm:h-12 w-auto drop-shadow-lg"
                     alt="Logo">

            </a>

            {{-- ================= DESKTOP MENU ================= --}}
            <nav class="hidden lg:flex items-center gap-3 xl:gap-5 font-semibold text-[#3e2723]">

                <a href="{{ route('home') }}"
                   class="px-3 py-2 rounded-xl hover:text-white hover:bg-[#3e2723]/80 transition">
                    Home
                </a>

                <a href="{{ route('about') }}"
                   class="px-3 py-2 rounded-xl hover:text-white hover:bg-[#3e2723]/80 transition">
                    About
                </a>

                <a href="{{ route('menu') }}"
                   class="px-3 py-2 rounded-xl hover:text-white hover:bg-[#3e2723]/80 transition">
                    Menu
                </a>

                <a href="{{ route('galeri') }}"
                   class="px-3 py-2 rounded-xl hover:text-white hover:bg-[#3e2723]/80 transition">
                    Galeri
                </a>

                <a href="{{ route('kontak') }}"
                   class="px-3 py-2 rounded-xl hover:text-white hover:bg-[#3e2723]/80 transition">
                    Contact
                </a>

                {{-- SUPER ADMIN --}}
                @auth
                    @if(auth()->user()->role === 'super_admin')

                    <a href="{{ route('admin.index') }}"
                       class="px-4 py-2 rounded-xl
                              bg-[#3e2723]/90
                              text-white
                              shadow-lg
                              hover:bg-[#2c1b18]
                              transition">

                        Kelola Admin

                    </a>

                    @endif
                @endauth

                {{-- USER --}}
                @auth

                <div class="relative" id="userDropdownWrapper">

                    {{-- Avatar --}}
                    <button id="userDropdownBtn"
                        class="ml-2 w-10 h-10 rounded-full
                               bg-[#3e2723]/90
                               text-white
                               flex items-center justify-center
                               shadow-lg
                               hover:scale-105
                               transition">

                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}

                    </button>

                    {{-- Dropdown --}}
                    <div id="userDropdown"
                         class="hidden absolute right-0 mt-3 w-60
                                backdrop-blur-2xl
                                bg-white/80
                                border border-white/40
                                rounded-3xl
                                shadow-2xl
                                overflow-hidden">

                        {{-- INFO --}}
                        <div class="px-5 py-4 border-b border-black/5">

                            <p class="font-semibold text-[#3e2723]">
                                {{ auth()->user()->nama }}
                            </p>

                            <p class="text-xs text-gray-500 mt-1 break-all">
                                {{ auth()->user()->email }}
                            </p>

                        </div>

                        {{-- PROFILE --}}
                        <a href="{{ route('profile') }}"
                           class="block px-5 py-3 text-sm
                                  hover:bg-[#3e2723]/10 transition">

                            Edit Akun

                        </a>

                        {{-- LOGOUT --}}
                        <form method="POST" action="{{ route('logout') }}">

                            @csrf

                            <button type="submit"
                                class="w-full text-left px-5 py-3 text-sm
                                       hover:bg-red-500/10
                                       text-red-500 transition">

                                Logout

                            </button>

                        </form>

                    </div>

                </div>

                @endauth

            </nav>

            {{-- ================= MOBILE BUTTON ================= --}}
            <button id="mobileMenuBtn"
                    class="lg:hidden w-11 h-11 rounded-xl
                           bg-white/40
                           backdrop-blur-xl
                           border border-white/30
                           flex items-center justify-center
                           text-[#3e2723]
                           shadow-lg">

                ☰

            </button>

        </div>

    </div>

    {{-- ================= MOBILE MENU ================= --}}
    {{-- ================= MOBILE MENU ================= --}}
<div id="mobileMenu"
     class="hidden lg:hidden absolute top-full left-0 w-full px-4 pt-3 pb-5">

    {{-- LIQUID GLASS CONTAINER --}}
    <div class="
        rounded-[2rem]
        border border-white/30
        bg-white/75
        backdrop-blur-3xl
        shadow-[0_20px_60px_rgba(0,0,0,0.18)]
        overflow-hidden
        relative
    ">

        {{-- GLOW EFFECT --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden">

            {{-- gold glow --}}
            <div class="absolute -top-10 left-10 w-40 h-40 bg-[#c8a97e]/30 blur-3xl rounded-full"></div>

            {{-- brown glow --}}
            <div class="absolute bottom-0 right-0 w-44 h-44 bg-[#3e2723]/20 blur-3xl rounded-full"></div>

            {{-- white shine --}}
            <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-transparent to-transparent opacity-60"></div>

        </div>

        {{-- MENU CONTENT --}}
            {{-- MENU CONTENT --}}
        <div class="relative px-5 py-5 space-y-3">

            {{-- STYLE MENU --}}
            @php
                $menuClass = "
                block px-4 py-3 rounded-2xl
                bg-[#3e2723]/90
                border border-[#ffffff]/10
                text-white font-semibold tracking-wide
                backdrop-blur-xl
                shadow-[0_10px_30px_rgba(62,39,35,0.35)]
                hover:bg-[#2c1b18]
                hover:scale-[1.02]
                hover:shadow-[0_14px_35px_rgba(62,39,35,0.45)]
                transition duration-300
                ";
            @endphp

            <a href="{{ route('home') }}"
            class="{{ $menuClass }}"
            style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.5);">
                Home
            </a>

            <a href="{{ route('about') }}"
            class="{{ $menuClass }}"
            style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.5);">
                About
            </a>

            <a href="{{ route('menu') }}"
            class="{{ $menuClass }}"
            style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.5);">
                Menu
            </a>

            <a href="{{ route('galeri') }}"
            class="{{ $menuClass }}"
            style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.5);">
                Galeri
            </a>

            <a href="{{ route('kontak') }}"
            class="{{ $menuClass }}"
            style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.5);">
                Contact
            </a>

            {{-- SUPER ADMIN MOBILE --}}
            @auth
                @if(auth()->user()->role === 'super_admin')

                <a href="{{ route('admin.index') }}"
                class="block px-4 py-3 rounded-2xl
                        bg-gradient-to-r from-[#c8a97e] to-[#a67c52]
                        border border-[#ffffff]/10
                        text-white font-semibold tracking-wide
                        shadow-[0_10px_30px_rgba(200,169,126,0.35)]
                        hover:scale-[1.02]
                        hover:brightness-110
                        transition duration-300"
                style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.45);">

                    Kelola Admin

                </a>

                @endif
            @endauth

            {{-- USER MOBILE --}}
            @auth

            <div class="pt-4 mt-4 border-t border-[#3e2723]/10">

                <div class="px-2 mb-4">

                    <p class="font-semibold text-[#3e2723]">
                        {{ auth()->user()->nama }}
                    </p>

                    <p class="text-sm text-[#6b4f4f] break-all">
                        {{ auth()->user()->email }}
                    </p>

                </div>

                <a href="{{ route('profile') }}"
                class="{{ $menuClass }}"
                style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.5);">

                    Edit Akun

                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-3">

                    @csrf

                    <button type="submit"
                        class="w-full text-left px-4 py-3 rounded-2xl
                            bg-red-600/90
                            border border-red-300/20
                            text-white font-semibold tracking-wide
                            backdrop-blur-xl
                            shadow-[0_10px_30px_rgba(220,38,38,0.35)]
                            hover:bg-red-700
                            hover:scale-[1.02]
                            hover:shadow-[0_14px_35px_rgba(220,38,38,0.45)]
                            transition duration-300"
                        style="-webkit-text-stroke: 0.3px rgba(0,0,0,0.45);">

                        Logout

                    </button>

                </form>

            </div>

            @endauth

        </div>

    </div>

</div>

</header>

{{-- ================= SCRIPT ================= --}}
<script>

    {{-- USER DROPDOWN --}}
    const btn = document.getElementById('userDropdownBtn');
    const menu = document.getElementById('userDropdown');

    if(btn){

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', function(e){

            if (!btn.contains(e.target) && !menu.contains(e.target)) {

                menu.classList.add('hidden');

            }

        });

    }

    {{-- MOBILE MENU --}}
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    mobileBtn.addEventListener('click', () => {

        mobileMenu.classList.toggle('hidden');

    });

</script>