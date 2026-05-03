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
        <nav class="flex items-center gap-6 font-semibold text-[#3e2723]">

            <a href="{{ route('home') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition">
                Home
            </a>

            <a href="{{ route('about') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition">
                About
            </a>

            <a href="{{ route('menu') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition">
                Menu
            </a>

            <a href="{{ route('galeri') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition">
                Galeri
            </a>

            <a href="{{ route('kontak') }}" 
               class="px-3 py-1 rounded-lg hover:text-white hover:bg-[#3e2723]/80 transition">
                Contact
            </a>

            {{-- 🔥 KHUSUS SUPER ADMIN --}}
            @auth
                @if(auth()->user()->role === 'super_admin')
                <a href="{{ route('admin.index') }}" 
                   class="px-3 py-1 rounded-lg bg-[#3e2723]/90 text-white shadow hover:bg-[#2c1b18] transition">
                    Kelola Admin
                </a>
                @endif
            @endauth

            {{-- ================= USER ================= --}}
            @auth
            <div class="relative" id="userDropdownWrapper">

                {{-- Avatar --}}
                <button id="userDropdownBtn" 
                    class="ml-4 w-10 h-10 rounded-full bg-[#3e2723]/90 text-white flex items-center justify-center shadow-lg hover:scale-105 transition">
                    
                    {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                </button>

                {{-- Dropdown --}}
                <div id="userDropdown"
                     class="hidden absolute right-0 mt-3 w-56 backdrop-blur-xl bg-white/80 border border-white/40 rounded-2xl shadow-xl overflow-hidden">

                    {{-- Info --}}
                    <div class="px-4 py-3 border-b text-sm">
                        <p class="font-semibold text-[#3e2723]">
                            {{ auth()->user()->nama }}
                        </p>
                        <p class="text-gray-500 text-xs">
                            {{ auth()->user()->email }}
                        </p>
                    </div>

                    {{-- Edit akun --}}
                    <a href="{{ route('profile') }}" 
                       class="block px-4 py-3 text-sm hover:bg-[#3e2723]/10 transition">
                        Edit Akun
                    </a>

                    

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-3 text-sm hover:bg-red-500/10 text-red-500 transition">
                            Logout
                        </button>
                    </form>

                </div>

            </div>
            @endauth

        </nav>

    </div>
</header>

{{-- JS DROPDOWN --}}
<script>
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
</script>