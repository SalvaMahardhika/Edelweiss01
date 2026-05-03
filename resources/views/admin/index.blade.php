<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Admin - Edelweiss</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-[#f3ebe3] via-[#e8dac9] to-[#dbc6ae] text-[#3e2723]">

@include('layouts.navbar')

<main class="pt-32 pb-20 relative">

    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="text-red-500 text-center mb-4">
            {{ implode(', ', $errors->all()) }}
        </div>
    @endif

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="text-center text-green-600 mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- TITLE --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold">Kelola Admin</h1>
        <p class="text-[#6b4f4f]">Manajemen akun admin sistem</p>
    </div>

    {{-- BUTTON --}}
    <div class="flex justify-center mb-12">
        <button onclick="openModal('addModal')" 
            class="px-8 py-3 rounded-xl bg-[#3e2723] text-white shadow-xl hover:scale-105 transition">
            + Tambah Admin
        </button>
    </div>

    {{-- GRID --}}
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 lg:grid-cols-3 gap-10">

        @foreach($admins->where('id_user','!=',1) as $user)

        <div class="backdrop-blur bg-white/40 border rounded-3xl p-6 shadow-xl">

            {{-- AVATAR --}}
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-[#3e2723] text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr($user->nama,0,1)) }}
            </div>

            {{-- INFO --}}
            <h3 class="text-center font-semibold">{{ $user->nama }}</h3>
            <p class="text-center text-sm text-[#6b4f4f]">{{ $user->email }}</p>

            {{-- STATUS --}}
            <div class="flex justify-center mt-3">
                <span class="text-xs px-3 py-1 rounded-full
                    {{ $user->status ? 'bg-green-200 text-green-700' : 'bg-red-200 text-red-700' }}">
                    {{ $user->status ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            {{-- ACTION --}}
            <div class="flex justify-center gap-2 mt-5">

                {{-- EDIT --}}
                <button onclick="openEdit({{ $user->id_user }}, '{{ $user->nama }}', '{{ $user->email }}')" 
                    class="px-3 py-1 text-xs rounded-lg bg-[#c8a97e] text-white">
                    Edit
                </button>

                {{-- TOGGLE STATUS --}}
                <form method="POST" action="{{ route('admin.update', $user->id_user) }}">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="status" value="{{ $user->status ? 0 : 1 }}">

                    <button type="submit"
                        class="relative w-12 h-6 rounded-full transition-all
                        {{ $user->status ? 'bg-[#3e2723]' : 'bg-red-400' }}">

                        <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all
                        {{ $user->status ? 'translate-x-6' : '' }}"></span>

                    </button>
                </form>

                {{-- DELETE --}}
                <button onclick="confirmDelete({{ $user->id_user }})"
                    class="px-3 py-1 text-xs rounded-lg bg-red-500 text-white">
                    Hapus
                </button>

            </div>

        </div>

        @endforeach

    </div>

</main>

{{-- MODAL ADD --}}
<div id="addModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">

    <div class="bg-white rounded-3xl p-8 w-96">

        <h2 class="text-xl font-semibold mb-4">Tambah Admin</h2>

        <form method="POST" action="{{ route('admin.store') }}">
            @csrf

            {{-- NAMA --}}
            <input name="nama" required
                placeholder="Nama"
                class="w-full mb-3 px-4 py-2 rounded-xl border">

            {{-- EMAIL --}}
            <input name="email" type="email" required
                placeholder="Email"
                class="w-full mb-3 px-4 py-2 rounded-xl border">

            {{-- PASSWORD --}}
            <input name="password" type="password" required
                placeholder="Password"
                class="w-full mb-4 px-4 py-2 rounded-xl border">
                            <div class="flex gap-2">
                <button type="button" onclick="closeModal('addModal')" 
                    class="flex-1 bg-gray-200 py-2 rounded-lg">
                    Batal
                </button>

                <button type="submit"
                    class="flex-1 bg-[#3e2723] text-white py-2 rounded-lg">
                    Simpan
                </button>
            </div>

        </form>

    </div>

</div>

{{-- ================= MODAL EDIT ================= --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">

    <div class="bg-white rounded-3xl p-8 w-96">

        <h2 class="text-xl font-semibold mb-4">Edit Admin</h2>

        <form method="POST" id="editForm">
            @csrf
            @method('PUT')

            {{-- NAMA --}}
            <input type="text" name="nama" id="editNama" required
                class="w-full mb-3 px-4 py-2 rounded-xl border">

            {{-- EMAIL --}}
            <input type="email" name="email" id="editEmail" required
                class="w-full mb-3 px-4 py-2 rounded-xl border">

            {{-- PASSWORD (OPSIONAL) --}}
            <input type="password" name="password"
                placeholder="Kosongkan jika tidak ingin ganti password"
                class="w-full mb-4 px-4 py-2 rounded-xl border">

            <div class="flex gap-2">
                <button type="button" onclick="closeModal('editModal')" 
                    class="flex-1 bg-gray-200 py-2 rounded-lg">
                    Batal
                </button>

                <button type="submit"
                    class="flex-1 bg-[#3e2723] text-white py-2 rounded-lg">
                    Update
                </button>
            </div>

        </form>

    </div>

</div>

{{-- ================= SCRIPT ================= --}}
<script>
function openModal(id){
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id){
    document.getElementById(id).classList.add('hidden');
}

function confirmDelete(id){
    if(confirm('Yakin hapus admin ini?')){
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/' + id;

        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="DELETE">
        `;

        document.body.appendChild(form);
        form.submit();
    }
}

function openEdit(id, nama, email){
    document.getElementById('editNama').value = nama;
    document.getElementById('editEmail').value = email;

    document.getElementById('editForm').action = '/admin/' + id;

    openModal('editModal');
}
</script>

</body>
</html>