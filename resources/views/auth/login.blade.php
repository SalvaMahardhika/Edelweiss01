<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - Edelweiss</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] overflow-hidden">

    {{-- BACKGROUND GLOW --}}
    <div class="absolute inset-0 -z-10">

        {{-- gold glow --}}
        <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] bg-[#c8a97e]/40 blur-[120px] rounded-full"></div>

        {{-- brown glow --}}
        <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-[#3e2723]/40 blur-[120px] rounded-full"></div>

    </div>

    {{-- CARD WRAPPER --}}
    <div class="relative w-full max-w-md">

        {{-- OUTER GLOW (BIAR NGAMBANG) --}}
        <div class="absolute inset-0 bg-white/30 blur-2xl rounded-[40px] opacity-50"></div>

        {{-- MAIN GLASS --}}
        <div class="relative backdrop-blur-[40px] bg-gradient-to-br from-white/40 via-white/20 to-white/10 border border-white/30 rounded-[40px] p-8 shadow-[0_20px_80px_rgba(0,0,0,0.25)]">

            {{-- GLASS SHINE --}}
            <div class="absolute inset-0 rounded-[40px] bg-gradient-to-tr from-white/30 via-transparent to-transparent opacity-40 pointer-events-none"></div>

            {{-- INNER LIGHT --}}
            <div class="absolute inset-0 rounded-[40px] border border-white/20 pointer-events-none"></div>

            {{-- CONTENT --}}
            <div class="relative">

                {{-- LOGO --}}
                <div class="text-center mb-8">
                    <img src="{{ asset('img/logo/logo1.png') }}" class="h-16 mx-auto mb-4 drop-shadow-xl">
                    <h1 class="text-xl font-semibold text-[#3e2723] tracking-wide">
                        Admin Edelweiss
                    </h1>
                    
                </div>

                {{-- ERROR --}}
                @if(session('error'))
                    <div class="mb-4 text-sm text-red-500 text-center">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- EMAIL --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Email</label>
                        <input type="email" name="email" required
                            class="w-full mt-2 px-4 py-3 rounded-xl 
                            bg-white/60 backdrop-blur-md 
                            border border-white/30 
                            focus:outline-none focus:ring-2 focus:ring-[#c8a97e] 
                            shadow-inner transition"
                            placeholder="Masukkan email">
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <label class="text-sm font-medium text-[#3e2723]">Password</label>
                        <input type="password" name="password" required
                            class="w-full mt-2 px-4 py-3 rounded-xl 
                            bg-white/60 backdrop-blur-md 
                            border border-white/30 
                            focus:outline-none focus:ring-2 focus:ring-[#c8a97e] 
                            shadow-inner transition"
                            placeholder="Masukkan password">
                    </div>

                    {{-- BUTTON --}}
                    <button type="submit"
                        class="w-full py-3 rounded-xl 
                        bg-gradient-to-r from-[#3e2723] to-[#2c1b18] 
                        text-white font-semibold 
                        hover:scale-[1.02] hover:shadow-xl 
                        transition duration-300">
                        Masuk
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>
</html>