<footer class="bg-[#3e2723] text-white py-6">
    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">

        <p class="text-sm">
            &copy; {{ date('Y') }} Edelweiss Bakery. All rights reserved.
        </p>

        <div class="flex gap-6 text-sm">
            <a href="{{ route('menu') }}" class="hover:text-[#d4af37] transition">Menu</a>
            <a href="{{ route('about') }}" class="hover:text-[#d4af37] transition">About</a>
            <a href="{{ route('kontak') }}" class="hover:text-[#d4af37] transition">Contact</a>
        </div>

    </div>
</footer>