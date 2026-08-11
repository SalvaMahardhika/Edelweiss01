@extends('admin_layouts.master')

@section('page_title', 'Manajemen Kategori')

@section('content')
<style>
    .dataTables_scrollBody {
        min-height: 280px !important;
        max-height: calc(100vh - 24rem) !important;
    }
    .dataTables_processing {
        display: none !important;
    }
</style>

<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- HEADER --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#3e2723]">Kategori Produk</h3>
            <p class="text-sm text-gray-500 mt-0.5">Atur pengelompokan menu dan status visibilitasnya.</p>
        </div>
        <button onclick="openModal()" class="px-5 py-2.5 bg-[#3e2723] text-white font-semibold rounded-xl text-sm shadow-md hover:bg-[#2c1b18] transition inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
    </div>

    {{-- BARISAN FILTER UI & SEARCH REALTIME --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 space-y-4">
        {{-- INPUT PENCARIAN REALTIME --}}
        <div class="relative w-full">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" id="filter_search" placeholder="Cari Nama Kategori atau Deskripsi..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
        </div>

        {{-- FILTER DROPDOWN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Status Visibilitas</label>
                <select id="filter_status" class="w-full mt-1 px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Non-Aktif</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Urutan Tampilan</label>
                <select id="filter_sort" class="w-full mt-1 px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="default">Urutan Default (Sort Order)</option>
                    <option value="name_asc">Nama (A - Z)</option>
                    <option value="latest">Terbaru Dibuat</option>
                </select>
            </div>

            <div>
                <button type="button" id="resetCategoryFilterBtn" class="w-full py-2.5 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center shadow-sm flex items-center justify-center gap-1">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 p-2 shadow-inner">
            <table id="categoryTable" class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4">Nama Kategori</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4 text-center">Status (Aktif)</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/30 text-sm font-medium">
                    {{-- Data dimuat dinamis via DataTables AJAX --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/60 backdrop-blur-3xl border border-white/50 shadow-2xl relative">
        <h3 class="text-lg font-bold text-[#3e2723] mb-4">Tambah Kategori</h3>
        <form id="addCategoryForm" class="space-y-4">
            @csrf
            <input type="text" id="add_name" name="name" placeholder="Nama Kategori" required class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/70 border border-white/50 focus:ring-2 focus:ring-[#c8a97e] outline-none">
            <textarea id="add_description" name="description" placeholder="Deskripsi Singkat" class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/70 border border-white/50 focus:ring-2 focus:ring-[#c8a97e] outline-none"></textarea>
            <div class="flex gap-2">
                <button type="button" onclick="closeModal()" class="flex-1 py-2 font-bold rounded-xl bg-white/50 border border-white/60 hover:bg-white transition">Batal</button>
                <button type="submit" id="btnAddSubmit" class="flex-1 py-2 font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="editModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/60 backdrop-blur-3xl border border-white/50 shadow-2xl relative">
        <h3 class="text-lg font-bold text-[#3e2723] mb-4">Edit Kategori</h3>
        <form id="editCategoryForm" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_id">
            <input type="text" id="edit_name" name="name" placeholder="Nama Kategori" required class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/70 border border-white/50 focus:ring-2 focus:ring-[#c8a97e] outline-none">
            <textarea id="edit_description" name="description" placeholder="Deskripsi Singkat" class="w-full px-4 py-2.5 text-sm rounded-xl bg-white/70 border border-white/50 focus:ring-2 focus:ring-[#c8a97e] outline-none"></textarea>
            <div class="flex gap-2">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-2 font-bold rounded-xl bg-white/50 border border-white/60 hover:bg-white transition">Batal</button>
                <button type="submit" id="btnEditSubmit" class="flex-1 py-2 font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- SISTEM ALERT MODAL (Sama dengan Dashboard) --}}
<div id="systemAlertModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="w-full max-w-sm p-6 rounded-[2rem] bg-white/50 backdrop-blur-3xl border border-white/60 shadow-2xl text-center space-y-4">
        <div id="alertIconContainer" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md bg-green-100 text-green-600">
            <i id="alertIcon" class="fa-solid fa-check"></i>
        </div>
        <p id="alertDescription" class="text-sm text-gray-700"></p>
        <div id="alertActionArea" class="flex gap-2 pt-2">
            <button onclick="closeSystemAlert()" class="w-full py-2.5 font-bold rounded-xl bg-[#3e2723] text-white">Oke</button>
        </div>
    </div>
</div>

<script>
    let categoryTable = null;
    let pendingDeleteUrl = null;
    let categorySearchTimer = null;

    function openModal() { 
        document.getElementById('addCategoryForm').reset();
        document.getElementById('addModal').classList.remove('hidden'); 
    }
    
    function closeModal() { 
        document.getElementById('addModal').classList.add('hidden'); 
    }

    function openEditModal(id, name, description, updateUrl) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('editCategoryForm').action = updateUrl;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function showToastAlert(message, isSuccess = true) {
        const modal = document.getElementById('systemAlertModal');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const desc = document.getElementById('alertDescription');
        const actionArea = document.getElementById('alertActionArea');

        desc.innerText = message;

        if (isSuccess) {
            iconContainer.className = "w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md bg-green-100 text-green-600";
            icon.className = "fa-solid fa-check";
        } else {
            iconContainer.className = "w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md bg-red-100 text-red-600";
            icon.className = "fa-solid fa-triangle-exclamation";
        }

        actionArea.innerHTML = `<button onclick="closeSystemAlert()" class="w-full py-2.5 font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition">Oke</button>`;
        modal.classList.remove('hidden');
    }

    function closeSystemAlert() { 
        document.getElementById('systemAlertModal').classList.add('hidden'); 
    }

    // ⚡ SWITCH STATUS TOGGLE VIA AJAX
    function toggleCategoryStatus(url) {
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PATCH'
            },
            success: function(res) {
                if (categoryTable) {
                    categoryTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                showToastAlert('Gagal memperbarui status kategori.', false);
            }
        });
    }

    // ⚡ TRIGGER KONFIRMASI HAPUS
    function triggerDelete(url) {
        pendingDeleteUrl = url;
        const modal = document.getElementById('systemAlertModal');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const desc = document.getElementById('alertDescription');
        const actionArea = document.getElementById('alertActionArea');

        iconContainer.className = "w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl shadow-md bg-amber-100 text-amber-700";
        icon.className = "fa-solid fa-trash-can";
        desc.innerText = "Yakin ingin menghapus kategori ini?";

        actionArea.innerHTML = `
            <button onclick="closeSystemAlert()" class="flex-1 py-2 font-bold rounded-xl bg-white/50 border border-white hover:bg-white transition">Batal</button>
            <button onclick="confirmDeleteProcess()" class="flex-1 py-2 font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition">Hapus</button>
        `;
        modal.classList.remove('hidden');
    }

    // ⚡ PROSES HAPUS KATEGORI VIA AJAX
    function confirmDeleteProcess() {
        if (!pendingDeleteUrl) return;

        $.ajax({
            url: pendingDeleteUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(res) {
                pendingDeleteUrl = null;
                showToastAlert(res.message || 'Kategori berhasil dihapus.', true);
                if (categoryTable) {
                    categoryTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                pendingDeleteUrl = null;
                showToastAlert('Gagal menghapus kategori.', false);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 1. INITIALIZE DATATABLES AJAX SERVER-SIDE
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            categoryTable = $('#categoryTable').DataTable({
                processing: false,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                dom: 'rtip',
                scrollY: 'calc(100vh - 24rem)',
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('kategori.index') }}",
                    type: "GET",
                    global: false,
                    data: function (d) {
                        d.custom_search = $('#filter_search').val();
                        d.status_filter = $('#filter_status').val();
                        d.sort_filter = $('#filter_sort').val();
                    }
                },
                columns: [
                    { data: 'name', name: 'name', className: 'font-bold text-[#3e2723]' },
                    { data: 'description', name: 'description', className: 'text-gray-600' },
                    { data: 'is_active', name: 'is_active', className: 'text-center', orderable: false, searchable: false },
                    { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
                ],
                language: {
                    search: "",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ kategori",
                    paginate: {
                        previous: "<i class='fa-solid fa-chevron-left'></i>",
                        next: "<i class='fa-solid fa-chevron-right'></i>"
                    }
                }
            });

            // 🔍 LISTENERS SEARCH & FILTER DEBOUNCE (400ms)
            $('#filter_search').off('keyup').on('keyup', function() {
                clearTimeout(categorySearchTimer);
                categorySearchTimer = setTimeout(function() {
                    categoryTable.draw();
                }, 400);
            });

            $('#filter_status, #filter_sort').off('change').on('change', function() {
                categoryTable.draw();
            });

            $('#resetCategoryFilterBtn').off('click').on('click', function() {
                $('#filter_search').val('');
                $('#filter_status').val('ALL');
                $('#filter_sort').val('default');
                categoryTable.draw();
            });
        }

        // 2. FORM TAMBAH KATEGORI VIA AJAX
        $('#addCategoryForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnAddSubmit');
            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: "{{ route('kategori.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Simpan');
                    closeModal();
                    showToastAlert(res.message || 'Kategori berhasil ditambahkan.', true);
                    if (categoryTable) {
                        categoryTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Simpan');
                    let errorMsg = 'Gagal menambahkan kategori.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errs = Object.values(xhr.responseJSON.errors);
                        if (errs.length > 0) errorMsg = errs[0][0];
                    }
                    showToastAlert(errorMsg, false);
                }
            });
        });

        // 3. FORM EDIT KATEGORI VIA AJAX
        $('#editCategoryForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnEditSubmit');
            const url = $(this).attr('action');
            btn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: url,
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Update');
                    closeEditModal();
                    showToastAlert(res.message || 'Kategori berhasil diupdate.', true);
                    if (categoryTable) {
                        categoryTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Update');
                    let errorMsg = 'Gagal mengupdate kategori.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errs = Object.values(xhr.responseJSON.errors);
                        if (errs.length > 0) errorMsg = errs[0][0];
                    }
                    showToastAlert(errorMsg, false);
                }
            });
        });
    });
</script>
@endsection