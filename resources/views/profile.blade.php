<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil - Edelweiss</title>
        <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] flex items-center justify-center overflow-hidden">

    {{-- BACKGROUND GLOW --}}
    <div class="absolute inset-0 -z-10 pointer-events-none">
        <div class="absolute top-1/3 left-1/4 w-[450px] h-[450px] bg-[#c8a97e]/30 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[450px] h-[450px] bg-[#3e2723]/30 blur-[120px] rounded-full"></div>
    </div>

    {{-- WRAPPER --}}
    <div class="relative w-full max-w-md px-6">

        {{-- OUTER GLOW --}}
        <div class="absolute inset-0 bg-white/20 blur-2xl rounded-3xl opacity-50 pointer-events-none"></div>

        {{-- MAIN CARD --}}
        <div class="relative backdrop-blur-[40px] bg-gradient-to-br from-white/40 via-white/20 to-white/10 border border-white/30 rounded-3xl p-8 shadow-[0_20px_80px_rgba(0,0,0,0.25)]">

            {{-- GLASS SHINE --}}
            <div class="absolute inset-0 rounded-3xl bg-gradient-to-tr from-white/30 via-transparent to-transparent opacity-40 pointer-events-none"></div>

            <div class="relative">

                {{-- TOP NAV (MASUK DALAM CARD) --}}
                <div class="flex justify-between items-center mb-6">

                    {{-- DASHBOARD --}}
                    <a href="{{ route('home') }}" 
                       class="px-4 py-2 text-sm rounded-full bg-white/50 backdrop-blur border border-white/40 text-[#3e2723] shadow hover:bg-[#3e2723] hover:text-white transition">
                        ← Dashboard
                    </a>

                    {{-- LOGOUT --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button 
                            class="px-4 py-2 text-sm rounded-full bg-red-400/80 text-white shadow hover:bg-red-600 transition">
                            Logout
                        </button>
                    </form>

                </div>

                {{-- HEADER --}}
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-[#3e2723] text-white flex items-center justify-center text-2xl font-bold shadow-xl">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </div>

                    <h2 class="text-xl font-semibold text-[#3e2723]">
                        Edit Profil
                    </h2>

                    <p class="text-xs text-[#6b4f4f]">
                        Kelola akun Anda dengan aman
                    </p>
                </div>

                {{-- ALERT --}}
                @if(session('error'))
                    <div class="mb-4 text-sm text-red-500 bg-red-100/50 p-3 rounded-xl text-center">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 text-sm text-green-600 bg-green-100/50 p-3 rounded-xl text-center">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf

                    {{-- NAMA --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Nama</label>
                        <input type="text" name="nama" value="{{ auth()->user()->nama }}"
                            class="w-full mt-1 px-4 py-3 rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Email</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}"
                            class="w-full mt-1 px-4 py-3 rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">
                    </div>

                    {{-- PASSWORD --}}
                    <div class="pt-3 border-t border-white/30">
                        <p class="text-xs text-gray-500 mb-3">
                            Kosongkan jika tidak ingin mengubah password
                        </p>

                        <input type="password" name="password_lama" placeholder="Password Lama"
                            class="w-full mb-3 px-4 py-3 rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">

                        <input type="password" name="password_baru" placeholder="Password Baru"
                            class="w-full mb-3 px-4 py-3 rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">

                        <input type="password" name="konfirmasi_password" placeholder="Konfirmasi Password"
                            class="w-full px-4 py-3 rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                    </div>

                    {{-- BUTTON --}}
                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-[#3e2723] to-[#2c1b18] text-white font-semibold hover:scale-[1.02] hover:shadow-xl transition">
                        Simpan Perubahan
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>
</html>