<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] overflow-y-auto px-4 py-8">

    {{-- BACKGROUND GLOW --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        {{-- gold glow --}}
        <div class="absolute top-1/3 left-1/4 w-[300px] sm:w-[400px] md:w-[500px] h-[300px] sm:h-[400px] md:h-[500px] bg-[#c8a97e]/40 blur-[100px] rounded-full"></div>
        {{-- brown glow --}}
        <div class="absolute bottom-1/4 right-1/4 w-[300px] sm:w-[400px] md:w-[500px] h-[300px] sm:h-[400px] md:h-[500px] bg-[#3e2723]/40 blur-[100px] rounded-full"></div>
    </div>

    {{-- CARD WRAPPER --}}
    <div class="relative w-full max-w-md my-auto">

        {{-- OUTER GLOW --}}
        <div class="absolute inset-0 bg-white/30 blur-2xl rounded-[30px] sm:rounded-[40px] opacity-50"></div>

        {{-- MAIN GLASS --}}
        <div class="relative backdrop-blur-[40px] bg-gradient-to-br from-white/40 via-white/20 to-white/10 border border-white/30 rounded-[30px] sm:rounded-[40px] p-6 sm:p-8 shadow-[0_20px_80px_rgba(0,0,0,0.25)]">

            {{-- GLASS SHINE --}}
            <div class="absolute inset-0 rounded-[30px] sm:rounded-[40px] bg-gradient-to-tr from-white/30 via-transparent to-transparent opacity-40 pointer-events-none"></div>

            {{-- INNER LIGHT --}}
            <div class="absolute inset-0 rounded-[30px] sm:rounded-[40px] border border-white/20 pointer-events-none"></div>

            {{-- CONTENT --}}
            <div class="relative">

                {{-- BACK BUTTON --}}
                <div class="mb-6">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/50 backdrop-blur border border-white/30 text-[#3e2723] text-sm font-medium hover:bg-white/70 hover:scale-105 transition">
                        ← Kembali ke Beranda
                    </a>
                </div>

                {{-- LOGO --}}
                <div class="text-center mb-6">
                    <img src="{{ asset('img/logo/logo1.png') }}"
                         class="h-14 sm:h-16 mx-auto mb-3 drop-shadow-xl">

                    <h1 class="text-lg sm:text-xl font-semibold text-[#3e2723] tracking-wide">
                        Daftar Akun Pelanggan
                    </h1>
                </div>

                {{-- VALIDATION ERRORS --}}
                @if($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-xs text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <p>• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('register.process') }}" class="space-y-4">
                    @csrf

                    {{-- NAMA LENGKAP --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Nama Lengkap</label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}"
                               required
                               class="w-full mt-1.5 px-4 py-2.5 rounded-xl bg-white/60 backdrop-blur-md border border-white/30 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition text-sm"
                               placeholder="Nama lengkap Anda">
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Email</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               required
                               class="w-full mt-1.5 px-4 py-2.5 rounded-xl bg-white/60 backdrop-blur-md border border-white/30 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition text-sm"
                               placeholder="contoh@email.com">
                    </div>

                    {{-- TELEPON --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Nomor Telepon (WhatsApp)</label>
                        <input type="text" 
                               name="phone" 
                               value="{{ old('phone') }}"
                               required
                               class="w-full mt-1.5 px-4 py-2.5 rounded-xl bg-white/60 backdrop-blur-md border border-white/30 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition text-sm"
                               placeholder="08xxxxxxxxxx">
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Password</label>
                        <input type="password" 
                               name="password" 
                               required
                               class="w-full mt-1.5 px-4 py-2.5 rounded-xl bg-white/60 backdrop-blur-md border border-white/30 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition text-sm"
                               placeholder="Minimal 8 karakter">
                    </div>

                    {{-- CONFIRM PASSWORD --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Konfirmasi Password</label>
                        <input type="password" 
                               name="password_confirmation" 
                               required
                               class="w-full mt-1.5 px-4 py-2.5 rounded-xl bg-white/60 backdrop-blur-md border border-white/30 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition text-sm"
                               placeholder="Ulangi password">
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <button type="submit"
                        class="w-full py-3 mt-2 rounded-xl bg-gradient-to-r from-[#3e2723] to-[#2c1b18] text-white font-semibold text-sm hover:scale-[1.02] hover:shadow-xl transition duration-300">
                        Daftar Sekarang
                    </button>

                    {{-- REDIRECT TO LOGIN --}}
                    <div class="text-center pt-2">
                        <p class="text-xs text-gray-500">
                            Sudah punya akun? 
                            <a href="{{ route('login') }}" class="font-bold text-[#3e2723] hover:underline">Masuk di sini</a>
                        </p>
                    </div>

                </form>

            </div>
        </div>
    </div>

</body>
</html>