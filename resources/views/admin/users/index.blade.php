@extends('admin_layouts.master')

@section('page_title', 'Manajemen Akun Pengguna')

@section('content')
{{-- AREA SCROLL MANDIRI PADA PANEL KONTEN UTAMA --}}
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- 1. STATISTIK RINGKASAN PENGGUNA --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-[#3e2723]/60 uppercase tracking-wider">Total Pengguna</p>
                <h3 class="text-2xl font-black text-[#3e2723] mt-1">{{ $users->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723]/10 text-[#3e2723] flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-800/60 uppercase tracking-wider">Administrator</p>
                <h3 class="text-2xl font-black text-amber-900 mt-1">{{ $users->whereIn('role', ['admin', 'super_admin'])->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-user-shield"></i>
            </div>
        </div>

        <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[1.5rem] p-5 shadow-xl flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-800/60 uppercase tracking-wider">Pelanggan / Customer</p>
                <h3 class="text-2xl font-black text-emerald-900 mt-1">{{ $users->where('role', 'customer')->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-800 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-user-tag"></i>
            </div>
        </div>
    </div>

    {{-- 2. HEADER INTERFACE & ACTION BAR --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#3e2723]">Daftar Pengguna Sistem</h3>
            <p class="text-sm text-gray-500 mt-0.5">Kelola hak akses dan status aktivasi seluruh akun pengguna.</p>
        </div>
        <button onclick="openAddUserModal()" class="px-5 py-2.5 bg-[#3e2723] text-white font-semibold rounded-xl text-sm shadow-md hover:bg-[#2c1b18] transition duration-300 inline-flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna Baru
        </button>
    </div>

    {{-- 3. TABLE DATA USERS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        
        {{-- FILTER & SEARCH BAR --}}
        <div class="mb-4 flex flex-col sm:flex-row gap-3 justify-between items-center">
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="userSearchInput" onkeyup="filterUsersTable()" placeholder="Cari nama atau email..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <span class="text-xs font-bold text-[#3e2723]/70 uppercase">Filter Role:</span>
                <select id="roleFilterSelect" onchange="filterUsersTable()" class="px-3 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-semibold">
                    <option value="ALL">Semua Peran</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="customer">Customer</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 shadow-inner">
            <table class="w-full text-left border-collapse" id="usersTable">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Kontak / Telepon</th>
                        <th class="px-6 py-4 text-center">Peran (Role)</th>
                        <th class="px-6 py-4 text-center">Status Akun</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    @foreach($users as $user)
                    <tr class="hover:bg-white/30 transition user-row" data-role="{{ $user->role }}" data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                        
                        {{-- ID & Nama --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#3e2723] text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-[#2d1f1b]">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 font-normal">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- No Telepon --}}
                        <td class="px-6 py-4 text-xs text-gray-600">
                            {{ $user->phone ?? '-' }}
                        </td>

                        {{-- Role Badge --}}
                        <td class="px-6 py-4 text-center">
                            @if($user->role === 'super_admin')
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-sm">
                                    <i class="fa-solid fa-crown text-[10px] mr-1"></i> Super Admin
                                </span>
                            @elseif($user->role === 'admin')
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-300 shadow-sm">
                                    <i class="fa-solid fa-user-shield text-[10px] mr-1"></i> Admin
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-300 shadow-sm">
                                    <i class="fa-solid fa-user text-[10px] mr-1"></i> Customer
                                </span>
                            @endif
                        </td>

                        {{-- Toggle Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($user->id === auth()->id())
                                <span class="text-xs text-gray-400 italic">Akun Anda</span>
                            @else
                                <form method="POST" action="{{ route('admin.update', $user->id) }}">
                                    @csrf
                                    @method('PUT')
                                    {{-- HANYA KIRIM FIELD STATUS AGAR DI-HANDLE OLEH LOGIKA TOGGLE DI CONTROLLER --}}
                                    <input type="hidden" name="status" value="{{ $user->status ? 0 : 1 }}">
                                    
                                    <button type="submit" class="relative inline-flex items-center h-6 w-12 rounded-full transition-colors duration-300 border border-white/30 {{ $user->status ? 'bg-green-500' : 'bg-red-500' }}" title="{{ $user->status ? 'Klik untuk Nonaktifkan' : 'Klik untuk Aktifkan' }}">
                                        <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-300 {{ $user->status ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            @endif
                        </td>

                        {{-- Aksi Edit & Hapus --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Tombol Edit Modal --}}
                                <button type="button" onclick="openEditUserModal({{ json_encode($user) }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Edit Akun">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>

                                {{-- Tombol Hapus --}}
                                @if($user->id !== auth()->id())
                                    <button type="button" onclick="triggerDelete('{{ route('admin.destroy', $user->id) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Hapus Akun">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 4. MODAL POPUP: TAMBAH USER BARU --}}
<div id="addUserModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-lg p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl relative space-y-4 my-auto">
        <div class="flex justify-between items-center pb-2 border-b border-[#3e2723]/15">
            <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-solid fa-user-plus mr-2"></i> Tambah Pengguna Baru</h3>
            <button type="button" onclick="closeAddUserModal()" class="w-8 h-8 rounded-full bg-white/40 flex items-center justify-center font-bold text-[#3e2723] hover:bg-white/70">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Email</label>
                    <input type="email" name="email" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Nomor Telepon</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx" class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Peran (Role)</label>
                    <select name="role" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition text-[#3e2723] font-medium">
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Kata Sandi (Password)</label>
                    <input type="password" name="password" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeAddUserModal()" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-white/60 border border-white/40 text-[#3e2723] hover:bg-white/80 transition">Batal</button>
                <button type="submit" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-[#3e2723] text-white shadow-md hover:bg-[#2c1b18] transition">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

{{-- 5. MODAL POPUP: EDIT USER --}}
<div id="editUserModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-lg p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl relative space-y-4 my-auto">
        <div class="flex justify-between items-center pb-2 border-b border-[#3e2723]/15">
            <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-solid fa-user-pen mr-2"></i> Edit Data Pengguna</h3>
            <button type="button" onclick="closeEditUserModal()" class="w-8 h-8 rounded-full bg-white/40 flex items-center justify-center font-bold text-[#3e2723] hover:bg-white/70">✕</button>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Email</label>
                    <input type="email" id="edit_email" name="email" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Nomor Telepon</label>
                    <input type="text" id="edit_phone" name="phone" class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Peran (Role)</label>
                    <select id="edit_role" name="role" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition text-[#3e2723] font-medium">
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Password Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Biarkan kosong jika tetap" class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditUserModal()" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-white/60 border border-white/40 text-[#3e2723] hover:bg-white/80 transition">Batal</button>
                <button type="submit" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-[#3e2723] text-white shadow-md hover:bg-[#2c1b18] transition">Perbarui Akun</button>
            </div>
        </form>
    </div>
</div>

{{-- 🚨 CUSTOM GLOBAL SYSTEM MODAL ALERT --}}
<div id="systemAlertModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl text-center space-y-4">
        <div id="alertIconContainer" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md">
            <i id="alertIcon" class="fa-solid"></i>
        </div>
        <div>
            <h4 id="alertTitle" class="text-lg font-bold text-[#3e2723]">Notifikasi</h4>
            <p id="alertDescription" class="text-sm text-gray-600 mt-1 leading-relaxed"></p>
        </div>
        <div id="alertActionArea" class="flex gap-2 pt-2">
            <button id="alertCloseBtn" onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-sm">
                Selesai
            </button>
        </div>
    </div>
</div>

{{-- Form Tersembunyi untuk Hapus --}}
<form id="globalDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // --- CONTROL MODAL TAMBAH USER ---
    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
    }
    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.add('hidden');
    }

    // --- CONTROL MODAL EDIT USER ---
    function openEditUserModal(user) {
        const form = document.getElementById('editUserForm');
        form.action = `/admin/users/${user.id}`;
        
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_role').value = user.role;

        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    // --- FILTER & SEARCH USER TABLE ---
    function filterUsersTable() {
        const searchVal = document.getElementById('userSearchInput').value.toLowerCase();
        const roleVal = document.getElementById('roleFilterSelect').value;
        const rows = document.querySelectorAll('.user-row');

        rows.forEach(row => {
            const matchesSearch = row.getAttribute('data-search').includes(searchVal);
            const matchesRole = (roleVal === 'ALL') || (row.getAttribute('data-role') === roleVal);

            if (matchesSearch && matchesRole) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // --- SYSTEM CUSTOM ALERT MODULE ---
    function openSystemAlert(title, message, type = 'success', confirmAction = null) {
        const modal = document.getElementById('systemAlertModal');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const titleEl = document.getElementById('alertTitle');
        const descEl = document.getElementById('alertDescription');
        const actionArea = document.getElementById('alertActionArea');

        titleEl.innerText = title;
        descEl.innerText = message;

        iconContainer.className = "w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md";
        actionArea.innerHTML = '';

        if (type === 'success') {
            iconContainer.classList.add('bg-green-100', 'text-green-600');
            icon.className = "fa-solid fa-circle-check";
            actionArea.innerHTML = `<button onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition shadow-sm">Oke</button>`;
        } else if (type === 'error') {
            iconContainer.classList.add('bg-red-100', 'text-red-600');
            icon.className = "fa-solid fa-triangle-exclamation";
            actionArea.innerHTML = `<button onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-sm">Tutup</button>`;
        } else if (type === 'confirm') {
            iconContainer.classList.add('bg-amber-100', 'text-amber-600');
            icon.className = "fa-solid fa-trash-can";
            actionArea.innerHTML = `
                <button onclick="closeSystemAlert()" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-white/60 border border-white text-[#3e2723] hover:bg-white/80 transition">Batal</button>
                <button id="alertConfirmSubmitBtn" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-sm">Hapus</button>
            `;
            document.getElementById('alertConfirmSubmitBtn').onclick = confirmAction;
        }

        modal.classList.remove('hidden');
    }

    function closeSystemAlert() {
        document.getElementById('systemAlertModal').classList.add('hidden');
    }

    // Handler Hapus
    function triggerDelete(actionUrl) {
        openSystemAlert(
            'Konfirmasi Hapus Akun',
            'Apakah Anda yakin ingin menghapus akun pengguna ini secara permanen?',
            'confirm',
            function() {
                const form = document.getElementById('globalDeleteForm');
                form.action = actionUrl;
                form.submit();
            }
        );
    }

    // --- NOTIFIKASI SESSION LARAVEL ---
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('success'))
            openSystemAlert('Berhasil!', "{{ session('success') }}", 'success');
        @endif

        @if($errors->any())
            openSystemAlert('Gagal Validasi', "{{ $errors->first() }}", 'error');
        @endif
    });
</script>
@endsection