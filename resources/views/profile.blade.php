<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil - Edelweiss Bakery</title>
    <link rel="icon" href="{{ asset('img/logo/logo2.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="min-h-screen bg-gradient-to-br from-[#f5f0ea] via-[#ede3d6] to-[#e6d8c7] flex items-center justify-center overflow-y-auto px-4 py-6">

    {{-- BACKGROUND GLOW --}}
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <div class="absolute top-1/4 left-1/3 w-[500px] h-[500px] bg-[#c8a97e]/25 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-1/4 right-1/3 w-[500px] h-[500px] bg-[#3e2723]/25 blur-[120px] rounded-full"></div>
    </div>

    {{-- WRAPPER --}}
    <div class="relative w-full max-w-2xl my-auto">

        {{-- OUTER GLOW --}}
        <div class="absolute inset-0 bg-white/20 blur-3xl rounded-[2.5rem] opacity-40 pointer-events-none"></div>

        {{-- MAIN CARD (LIQUID GLASS) --}}
        <div class="relative backdrop-blur-[40px] bg-gradient-to-br from-white/50 via-white/30 to-white/15 border border-white/40 rounded-[2.5rem] p-6 sm:p-8 shadow-[0_25px_70px_rgba(62,39,35,0.15)]">

            {{-- GLASS SHINE --}}
            <div class="absolute inset-0 rounded-[2.5rem] bg-gradient-to-tr from-white/30 via-transparent to-transparent opacity-40 pointer-events-none"></div>

            <div class="relative space-y-6">

                {{-- TOP NAV COMPONENT --}}
                <div class="flex justify-between items-center pb-4 border-b border-white/30">
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-white/60 border border-white/40 text-[#3e2723] shadow-sm hover:bg-[#3e2723] hover:text-white transition duration-300">
                        <i class="fa-solid fa-arrow-left"></i> Beranda
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-red-500/80 text-white shadow-sm hover:bg-red-600 transition duration-300">
                            <i class="fa-solid fa-right-from-bracket"></i> Keluar
                        </button>
                    </form>
                </div>

                {{-- ALERTS BLOCK --}}
                @if(session('error'))
                    <div class="text-sm font-medium text-red-600 bg-red-500/10 border border-red-500/20 p-3.5 rounded-2xl text-center">
                        <i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="text-sm font-medium text-green-600 bg-green-500/10 border border-green-500/20 p-3.5 rounded-2xl text-center">
                        <i class="fa-solid fa-circle-check mr-1.5"></i> {{ session('success') }}
                    </div>
                @endif

                {{-- FORM GRID ARSITEKTUR --}}
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        {{-- LEFT COLUMN: USER META PROFILE VISUAL --}}
                        <div class="md:col-span-1 flex flex-col items-center justify-center p-4 rounded-2xl bg-white/30 border border-white/20 shadow-inner">
                            <div class="w-20 h-20 rounded-full bg-[#3e2723] text-white flex items-center justify-center text-3xl font-black shadow-xl ring-4 ring-white/40">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <h3 class="text-lg font-bold text-[#3e2723] mt-3 text-center truncate w-full">
                                {{ auth()->user()->name }}
                            </h3>
                            <span class="px-3 py-1 text-[10px] uppercase font-black tracking-wider rounded-full bg-[#3e2723]/10 text-[#3e2723] mt-1.5">
                                {{ auth()->user()->role }}
                            </span>
                        </div>

                        {{-- RIGHT COLUMN: DUA KOLO DATA UTAMA INTERFACE --}}
                        <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            {{-- NAME FIELD --}}
                            <div class="sm:col-span-2">
                                <label class="text-xs font-bold text-[#3e2723]/80 uppercase tracking-wide">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ auth()->user()->name }}" required
                                    class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">
                            </div>

                            {{-- EMAIL FIELD --}}
                            <div>
                                <label class="text-xs font-bold text-[#3e2723]/80 uppercase tracking-wide">Alamat Email</label>
                                <input type="email" name="email" value="{{ auth()->user()->email }}" required
                                    class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition">
                            </div>

                            {{-- PHONE FIELD (INFORMASI TAMBAHAN) --}}
                            <div>
                                <label class="text-xs font-bold text-[#3e2723]/80 uppercase tracking-wide">Nomor Telepon (WA)</label>
                                <input type="text" name="phone" value="{{ auth()->user()->phone }}" required
                                    class="w-full mt-1.5 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] shadow-inner transition"
                                    placeholder="08xxxxxxxxxx">
                            </div>

                        </div>
                    </div>

                    {{-- PASSWORD MODIFICATION MODULE --}}
                    <div class="pt-5 border-t border-white/30 space-y-3">
                        <div>
                            <h4 class="text-sm font-bold text-[#3e2723]"><i class="fa-solid fa-shield-halved mr-1.5"></i> Perbarui Kata Sandi</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Kosongkan kolom di bawah ini jika tidak ada rencana mengubah password lama Anda.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <input type="password" name="password_lama" placeholder="Password Lama"
                                class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">

                            <input type="password" name="password_baru" placeholder="Password Baru"
                                class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">

                            <input type="password" name="konfirmasi_password" placeholder="Konfirmasi Password"
                                class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                        </div>
                    </div>

                    {{-- SUBMIT INTERFACE BUTTON --}}
                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-[#3e2723] to-[#2c1b18] text-white font-semibold text-sm shadow-md hover:scale-[1.01] hover:shadow-xl transition duration-300">
                        <i class="fa-regular fa-floppy-disk mr-1.5"></i> Simpan Perubahan Profil
                    </button>

                </form>

            </div>
        </div>
    </div>

</body>
</html>