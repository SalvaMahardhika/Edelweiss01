@extends('admin_layouts.master')

@section('page_title', 'Manajemen Galeri Portofolio')

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

{{-- AREA SCROLL MANDIRI PANEL UTAMA CMS --}}
<div class="max-h-[calc(100vh-8rem)] overflow-y-auto pr-2 space-y-6">

    {{-- HEADER MANAGEMENT INTERFACE --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#3e2723]">Daftar Album Galeri</h3>
            <p class="text-sm text-gray-500 mt-0.5">Kelola portofolio kue kustom, dokumentasi dapur, dan momen pameran Edelweiss Bakery.</p>
        </div>
        <button onclick="openAddModal()" class="px-5 py-2.5 bg-[#3e2723] text-white font-semibold rounded-xl text-sm shadow-md hover:bg-[#2c1b18] transition duration-300 inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Buat Album Baru
        </button>
    </div>

    {{-- BARISAN FILTER UI & SEARCH REALTIME --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-5 space-y-4">
        {{-- INPUT PENCARIAN REALTIME --}}
        <div class="relative w-full">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" id="filter_search" placeholder="Cari Judul Album atau Deskripsi..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-medium text-[#3e2723]">
        </div>

        {{-- FILTER DROPDOWN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Kepadatan Foto Album</label>
                <select id="filter_photo_count" class="w-full mt-1 px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="ALL">Semua Album</option>
                    <option value="empty">Kosong (0 Foto)</option>
                    <option value="compact">Ringkas (1 - 5 Foto)</option>
                    <option value="full">Lengkap (> 5 Foto)</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Urutan Tanggal Terbit</label>
                <select id="filter_date_sort" class="w-full mt-1 px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] text-[#3e2723] font-medium">
                    <option value="latest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="this_month">Diterbitkan Bulan Ini</option>
                </select>
            </div>

            <div>
                <button type="button" id="resetGaleriFilterBtn" class="w-full py-2.5 px-3 bg-white/60 border border-white text-xs font-bold rounded-xl text-[#3e2723] hover:bg-white transition text-center shadow-sm flex items-center justify-center gap-1">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- TABLE DATA PUSAT GALERI CMS --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] shadow-xl p-6 overflow-hidden">
        <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/20 p-2 shadow-inner">
            <table id="galeriTable" class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-white/40 text-xs font-bold uppercase tracking-wider text-[#3e2723]/70">
                        <th class="px-6 py-4 w-24 text-center">Sampul</th>
                        <th class="px-6 py-4">Judul Album</th>
                        <th class="px-6 py-4">Deskripsi Cerita</th>
                        <th class="px-6 py-4 text-center">Jumlah Foto</th>
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

{{-- COMPONENT: MODAL POPUP INPUT ALBUM BARU --}}
<div id="addAlbumModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full max-w-lg p-6 rounded-[2rem] bg-white/60 backdrop-blur-3xl border border-white/50 shadow-2xl relative space-y-4 my-auto">
        <div class="flex justify-between items-center pb-2 border-b border-[#3e2723]/15">
            <h3 class="text-lg font-bold text-[#3e2723]"><i class="fa-regular fa-images mr-2"></i> Form Pembuatan Album Baru</h3>
            <button type="button" onclick="closeAddModal()" class="w-8 h-8 rounded-full bg-white/40 flex items-center justify-center font-bold text-[#3e2723] hover:bg-white/70">✕</button>
        </div>

        <form id="addAlbumForm" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Judul Portofolio Album</label>
                <input type="text" id="draft_judul" name="judul" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/70 border border-white/50 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition">
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Deskripsi / Cerita Album Kue</label>
                <textarea id="draft_deskripsi" name="deskripsi" rows="4" required class="w-full mt-1 px-4 py-2.5 text-sm rounded-xl bg-white/70 border border-white/50 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] transition"></textarea>
            </div>

            <div>
                <label class="text-xs font-bold text-[#3e2723]/80 uppercase">Unggah Berkas Gambar (Bisa Banyak Sekaligus)</label>
                <input type="file" id="draft_gambar" name="gambar[]" multiple required class="w-full mt-1 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#3e2723] file:text-white file:cursor-pointer">
                <p class="text-[10px] text-gray-500 mt-1">*Sistem otomatis mengonversi gambar ke WebP dan merampingkan resolusi.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeAddModal()" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-white/60 border border-white/40 text-[#3e2723] hover:bg-white/80 transition">Batal</button>
                <button type="submit" id="btnSubmitAlbum" class="flex-1 py-3 text-sm font-semibold rounded-xl bg-[#3e2723] text-white shadow-md hover:bg-[#2c1b18] transition">Terbitkan Album</button>
            </div>
        </form>
    </div>
</div>

{{-- CUSTOM GLOBAL SYSTEM MODAL ALERT --}}
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
            <button onclick="closeSystemAlert()" class="w-full py-2.5 text-sm font-bold rounded-xl bg-[#3e2723] text-white hover:bg-[#2c1b18] transition">Oke</button>
        </div>
    </div>
</div>

<script>
    let galeriTable = null;
    let pendingDeleteUrl = null;
    let galeriPollingTimer = null;
    let galeriSearchTimer = null;

    function openAddModal() {
        document.getElementById('addAlbumModal').classList.remove('hidden');
    }
    
    function closeAddModal() {
        document.getElementById('addAlbumModal').classList.add('hidden');
    }

    const inputJudul = document.getElementById('draft_judul');
    const inputDeskripsi = document.getElementById('draft_deskripsi');

    if (localStorage.getItem('cms_galeri_judul')) inputJudul.value = localStorage.getItem('cms_galeri_judul');
    if (localStorage.getItem('cms_galeri_deskripsi')) inputDeskripsi.value = localStorage.getItem('cms_galeri_deskripsi');

    inputJudul.addEventListener('input', () => localStorage.setItem('cms_galeri_judul', inputJudul.value));
    inputDeskripsi.addEventListener('input', () => localStorage.setItem('cms_galeri_deskripsi', inputDeskripsi.value));

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
                <button id="confirmSubmitBtn" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-sm">Hapus</button>
            `;
            document.getElementById('confirmSubmitBtn').onclick = confirmAction;
        }
        modal.classList.remove('hidden');
    }

    function closeSystemAlert() {
        document.getElementById('systemAlertModal').classList.add('hidden');
    }

    function triggerDelete(actionUrl) {
        pendingDeleteUrl = actionUrl;
        openSystemAlert(
            'Hapus Album Permanen',
            'Apakah Anda yakin ingin menghapus album portofolio ini beserta seluruh aset foto fisik di dalamnya?',
            'confirm',
            function() {
                if (!pendingDeleteUrl) return;
                
                $.ajax({
                    url: pendingDeleteUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        closeSystemAlert();
                        openSystemAlert('Berhasil!', res.message || 'Album berhasil dihapus.', 'success');
                        if (galeriTable) galeriTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        closeSystemAlert();
                        openSystemAlert('Gagal Hapus', 'Terjadi kesalahan saat menghapus album.', 'error');
                    }
                });
            }
        );
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            galeriTable = $('#galeriTable').DataTable({
                processing: false,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                dom: 'rtip',
                scrollY: 'calc(100vh - 24rem)',
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('galeri.index') }}",
                    type: "GET",
                    global: false,
                    data: function (d) {
                        d.custom_search = $('#filter_search').val();
                        d.date_sort = $('#filter_date_sort').val();
                        d.photo_filter = $('#filter_photo_count').val();
                    }
                },
                columns: [
                    { data: 'cover', name: 'cover', className: 'text-center', orderable: false, searchable: false },
                    { data: 'judul', name: 'judul' },
                    { data: 'deskripsi', name: 'deskripsi' },
                    { data: 'total_photos', name: 'total_photos', className: 'text-center', orderable: false, searchable: false },
                    { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
                ],
                language: {
                    search: "",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ album",
                    paginate: {
                        previous: "<i class='fa-solid fa-chevron-left'></i>",
                        next: "<i class='fa-solid fa-chevron-right'></i>"
                    }
                }
            });

            $('#filter_search').off('keyup').on('keyup', function() {
                clearTimeout(galeriSearchTimer);
                galeriSearchTimer = setTimeout(function() {
                    galeriTable.draw();
                }, 400);
            });

            $('#filter_date_sort, #filter_photo_count').off('change').on('change', function() {
                galeriTable.draw();
            });

            $('#resetGaleriFilterBtn').off('click').on('click', function() {
                $('#filter_search').val('');
                $('#filter_photo_count').val('ALL');
                $('#filter_date_sort').val('latest');
                galeriTable.draw();
            });

            if (galeriPollingTimer) clearInterval(galeriPollingTimer);
            galeriPollingTimer = setInterval(function () {
                const isModalOpen = !$('#addAlbumModal').hasClass('hidden') || !$('#systemAlertModal').hasClass('hidden');
                const isUserFocusInput = $('#filter_search').is(':focus');
                
                if (galeriTable && document.visibilityState === 'visible' && !isModalOpen && !isUserFocusInput) {
                    let settings = galeriTable.settings()[0];
                    if (settings.jqXHR && settings.jqXHR.readyState !== 4) return;

                    let oldProcessing = settings.oFeatures.bProcessing;
                    settings.oFeatures.bProcessing = false;

                    galeriTable.ajax.reload(function() {
                        settings.oFeatures.bProcessing = oldProcessing;
                    }, false);
                }
            }, 3000);
        }

        $('#addAlbumForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnSubmitAlbum');
            btn.prop('disabled', true).text('Menerbitkan...');

            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('galeri.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    btn.prop('disabled', false).text('Terbitkan Album');
                    closeAddModal();
                    
                    localStorage.removeItem('cms_galeri_judul');
                    localStorage.removeItem('cms_galeri_deskripsi');
                    $('#addAlbumForm')[0].reset();

                    openSystemAlert('Berhasil!', res.message || 'Album portofolio berhasil diterbitkan.', 'success');
                    if (galeriTable) galeriTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Terbitkan Album');
                    let msg = 'Gagal menerbitkan album baru.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    openSystemAlert('Validasi Gagal', msg, 'error');
                }
            });
        });

        @if(session('success'))
            openSystemAlert('Berhasil!', "{{ session('success') }}", 'success');
        @endif
        @if($errors->any())
            openSystemAlert('Validasi Gagal', "{{ $errors->first() }}", 'error');
        @endif
    });
</script>
@endsection