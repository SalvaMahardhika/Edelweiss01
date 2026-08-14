@extends('admin_layouts.master')

@section('page_title', 'Input Pesanan Offline / Rekap')

@section('content')
<div x-data="offlineOrderApp({{ json_encode($products) }}, '{{ session('success') }}')" class="min-h-full flex flex-col space-y-6 pb-8 overflow-y-auto pr-2 relative">

    {{-- MODAL NOTIFIKASI SUKSES MANDIRI --}}
    <div x-show="showSuccessModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-md" 
         style="display: none;">
        
        <div class="bg-white/90 border border-white/80 backdrop-blur-2xl rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl text-center space-y-5">
            <div class="w-16 h-16 bg-emerald-500/10 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl border border-emerald-500/20 shadow-inner">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            
            <div class="space-y-1.5">
                <h3 class="text-lg font-black text-[#3e2723]">Transaksi Berhasil Disimpan!</h3>
                <p class="text-xs font-semibold text-gray-600" x-text="successMessage"></p>
            </div>

            <div class="pt-2">
                <button @click="closeSuccessModal()" type="button" class="w-full py-3 bg-[#3e2723] hover:bg-[#2c1b18] text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Input Transaksi Baru Lagi
                </button>
            </div>
        </div>
    </div>

    {{-- ALERT ERROR FLASH SESSION (EXCEPTIONS / MANUAL FAILURES) --}}
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 text-xs font-bold flex items-start justify-between shadow-lg">
        <div class="flex items-start gap-2.5">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base mt-0.5"></i>
            <div>
                <p class="font-black text-sm">Gagal Menyimpan Pesanan Offline:</p>
                <p class="mt-1 text-rose-800 font-semibold">{{ session('error') }}</p>
            </div>
        </div>
        <button @click="show = false" type="button" class="text-rose-700 hover:text-rose-950">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>
    @endif

    {{-- ALERT ERROR VALIDASI SERVER-SIDE --}}
    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-900 text-xs font-bold flex items-start justify-between shadow-lg">
        <div class="flex items-start gap-2.5">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base mt-0.5"></i>
            <div>
                <p class="font-black text-sm">Gagal Menyimpan Pesanan Offline:</p>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-rose-800 font-semibold">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button @click="show = false" type="button" class="text-rose-700 hover:text-rose-950">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>
    @endif

    {{-- HEADER BAR HALAMAN --}}
    <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-[#3e2723] text-white flex items-center justify-center text-xl shadow-lg shrink-0">
                <i class="fa-solid fa-file-circle-plus"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-[#3e2723]">Form Transaksi Offline / Rekap Toko</h3>
                <p class="text-xs font-semibold text-gray-600">Catat transaksi kasir langsung, pesanan offline, atau rekap penjualan lampau</p>
            </div>
        </div>
        <a href="{{ route('admin.orders.history') }}" class="px-4 py-2.5 bg-white/60 hover:bg-white text-[#3e2723] border border-white/60 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i> History Pesanan
        </a>
    </div>

    {{-- FORM UTAMA --}}
    <form action="{{ route('admin.orders.offline_store') }}" method="POST" @submit="handleSubmit($event)">
        @csrf

        {{-- INPUT HIDDEN TANGGAL UNTUK DATABASE (Format YYYY-MM-DD 12:00:00) --}}
        <input type="hidden" name="placed_at" id="placed_at_hidden" :value="convertToDbDate(placedAtText)">
        <input type="hidden" name="fulfill_at" id="fulfill_at_hidden" :value="convertToDbDate(fulfillAtText)">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- KOLOM KIRI (2/3): INFORMASI PELANGGAN, WAKTU & ITEM BELANJA --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- PANEL 1: DATA PELANGGAN & WAKTU --}}
                <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-wider text-[#3e2723] flex items-center gap-2 border-b border-white/50 pb-3">
                        <i class="fa-solid fa-user-gear text-amber-800 text-sm"></i> Informasi Pelanggan & Waktu Transaksi
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nama Pelanggan --}}
                        <div>
                            <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">
                                Nama Pelanggan <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" name="customer_name" x-model="customerName" required
                                class="w-full px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-semibold text-[#3e2723]">
                        </div>

                        {{-- No. WhatsApp --}}
                        <div>
                            <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">
                                No. WhatsApp / HP <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" name="customer_phone" x-model="customerPhone" required
                                class="w-full px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-semibold text-[#3e2723]">
                        </div>

                        {{-- Email (Opsional - Auto Dummy) --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">
                                Email Pelanggan <span class="text-gray-400 font-normal lowercase">(opsional - otomatis terisi email internal jika kosong)</span>
                            </label>
                            <input type="email" name="customer_email" x-model="customerEmail" placeholder="Contoh: pemesan@gmail.com"
                                class="w-full px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-semibold text-[#3e2723]">
                        </div>

                        {{-- Tanggal Pemesanan / Pembelian (Format dd/mm/yyyy) --}}
                        <div>
                            <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">
                                Tanggal Beli <span class="text-[#3e2723]/50 font-normal">(DD/MM/YYYY)</span> <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" x-model="placedAtText" @input="formatDateInput($event, 'placedAtText')" placeholder="dd/mm/yyyy" maxlength="10" required
                                class="w-full px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-bold text-[#3e2723]">
                        </div>

                        {{-- Tanggal Pengambilan / Penyerahan (Format dd/mm/yyyy) --}}
                        <div>
                            <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">
                                Tanggal Ambil <span class="text-[#3e2723]/50 font-normal">(DD/MM/YYYY)</span> <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" x-model="fulfillAtText" @input="formatDateInput($event, 'fulfillAtText')" placeholder="dd/mm/yyyy" maxlength="10" required
                                class="w-full px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-bold text-[#3e2723]">
                        </div>
                    </div>
                </div>

                {{-- PANEL 2: ITEM BELANJA / KERANJANG REKAP --}}
                <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-white/50 pb-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-[#3e2723] flex items-center gap-2">
                            <i class="fa-solid fa-basket-shopping text-emerald-800 text-sm"></i> Daftar Item Produk yang Dibeli
                        </h4>
                        
                        {{-- Tombol Tambah Item (Nonaktif jika item melebihi total produk unik) --}}
                        <button type="button" @click="addItem()" 
                            :disabled="items.length >= productList.length"
                            :class="items.length >= productList.length ? 'bg-gray-400 cursor-not-allowed opacity-60' : 'bg-[#3e2723] hover:bg-[#2c1b18]'"
                            class="px-3 py-1.5 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                            <i class="fa-solid fa-plus"></i> Tambah Item
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 rounded-2xl bg-white/50 border border-white/60 shadow-sm relative transition">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                    
                                    {{-- Pilih Produk --}}
                                    <div class="sm:col-span-5">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Produk Menu</label>
                                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductSelect(index)" required
                                            class="w-full px-3 py-2 text-xs rounded-xl bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-bold text-[#3e2723]">
                                            <option value="" disabled>-- Pilih Produk --</option>
                                            <template x-for="p in productList" :key="p.id">
                                                <option :value="p.id" 
                                                    :disabled="isProductSelected(p.id, index)"
                                                    x-text="p.nama_produk + ' (Rp ' + formatNumber(p.harga) + ')' + (isProductSelected(p.id, index) ? ' — [Pilihan Terpakai]' : '')">
                                                </option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Custom Price / Harga Satuan --}}
                                    <div class="sm:col-span-3">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Harga Satuan (Rp)</label>
                                        <input type="number" step="0.01" min="0" :name="'items[' + index + '][price]'" x-model.number="item.price" required
                                            class="w-full px-3 py-2 text-xs rounded-xl bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-bold text-[#3e2723]">
                                    </div>

                                    {{-- Jumlah / Qty --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Qty</label>
                                        <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" required
                                            class="w-full px-3 py-2 text-xs rounded-xl bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-bold text-center text-[#3e2723]">
                                    </div>

                                    {{-- Subtotal & Tombol Hapus --}}
                                    <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0">
                                        <div class="text-right">
                                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Subtotal</span>
                                            <span class="text-xs font-black text-amber-900" x-text="'Rp ' + formatNumber(item.price * item.quantity)"></span>
                                        </div>

                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" title="Hapus Item"
                                            class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg border border-rose-200 text-xs transition shrink-0">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN (1/3): TIPE LAYANAN, PEMBAYARAN, & RINGKASAN TOTAL --}}
            <div class="space-y-6">

                {{-- PANEL 3: METODE PENGAMBILAN & SKEMA BAYAR --}}
                <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-wider text-[#3e2723] flex items-center gap-2 border-b border-white/50 pb-3">
                        <i class="fa-solid fa-sliders text-purple-800 text-sm"></i> Layanan & Skema
                    </h4>

                    {{-- Tipe Pesanan --}}
                    <div>
                        <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">Tipe Penyerahan</label>
                        <select name="order_type" x-model="orderType" required
                            class="w-full px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-bold text-[#3e2723]">
                            <option value="pickup">Ambil Sendiri (Pickup Toko)</option>
                            <option value="delivery">Pengiriman Alamat (Delivery)</option>
                        </select>
                    </div>

                    {{-- Alamat Pengiriman (Jika Delivery) --}}
                    <div x-show="orderType === 'delivery'" x-transition>
                        <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">Alamat Tujuan Lengkap</label>
                        <textarea name="delivery_address" x-model="deliveryAddress" rows="3" placeholder="Masukkan alamat lengkap pengiriman..."
                            class="w-full px-3 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-semibold text-[#3e2723]"></textarea>
                    </div>

                    {{-- Skema Pembayaran --}}
                    <div>
                        <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">Skema Pembayaran Rekap</label>
                        <select name="payment_plan" x-model="paymentPlan" required
                            class="w-full px-4 py-2.5 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-bold text-[#3e2723]">
                            <option value="full">Full Payment (Lunas Langsung)</option>
                            <option value="dp">Skema DP (Uang Muka 50%)</option>
                        </select>
                    </div>

                    {{-- Catatan Pesanan --}}
                    <div>
                        <label class="block text-xs font-bold text-[#3e2723]/80 uppercase mb-1">Catatan Keseluruhan</label>
                        <textarea name="notes" x-model="notes" rows="2" placeholder="Catatan rekap/kasir..."
                            class="w-full px-3 py-2 text-xs rounded-xl bg-white/60 border border-white/40 focus:outline-none focus:ring-2 focus:ring-[#c8a97e] font-semibold text-[#3e2723]"></textarea>
                    </div>
                </div>

                {{-- PANEL 4: RINGKASAN KALKULASI & ACTION BUTTON --}}
                <div class="backdrop-blur-2xl bg-white/40 border border-white/50 rounded-[2rem] p-6 shadow-xl space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-wider text-[#3e2723] border-b border-white/50 pb-3">
                        Kalkulasi Tagihan Offline
                    </h4>

                    <div class="space-y-2 text-xs font-semibold text-[#3e2723]">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal Belanja</span>
                            <span class="font-bold" x-text="'Rp ' + formatNumber(calculateSubtotal())"></span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Pajak (0% Non-PPN Store)</span>
                            <span class="font-bold">Rp 0</span>
                        </div>

                        <template x-if="paymentPlan === 'dp'">
                            <div class="flex justify-between text-amber-900 bg-amber-500/10 p-2 rounded-xl border border-amber-400/30">
                                <span>Nilai DP Penanda (50%)</span>
                                <span class="font-black" x-text="'Rp ' + formatNumber(calculateSubtotal() * 0.5)"></span>
                            </div>
                        </template>

                        <div class="border-t border-white/60 pt-3 flex justify-between items-center text-sm font-black">
                            <span class="uppercase tracking-wider">Total Dibayar</span>
                            <span class="text-lg text-emerald-900" x-text="'Rp ' + formatNumber(calculateSubtotal())"></span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="isSubmitting"
                            class="w-full py-3.5 bg-[#3e2723] hover:bg-[#2c1b18] text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <i x-show="!isSubmitting" class="fa-solid fa-floppy-disk text-base"></i>
                            <i x-show="isSubmitting" class="fa-solid fa-circle-notch fa-spin text-base" style="display: none;"></i>
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi Offline'"></span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>

</div>

{{-- SCRIPT ALPINEJS KALKULASI INTERAKTIF & FORMATTING TANGGAL --}}
<script>
    function offlineOrderApp(productsData, flashSuccessMsg) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        const defaultDateStr = `${dd}/${mm}/${yyyy}`;

        return {
            productList: productsData || [],
            
            // Model State Pelanggan
            customerName: 'Pelanggan Toko Offline',
            customerPhone: '080000000000',
            customerEmail: '',
            
            // Model State Layanan & Opsi
            orderType: 'pickup',
            deliveryAddress: '',
            paymentPlan: 'full',
            notes: '',
            
            // Model State Waktu
            placedAtText: defaultDateStr,
            fulfillAtText: defaultDateStr,
            
            // Model State Item
            items: [
                { product_id: '', price: 0, quantity: 1 }
            ],

            // Modal & Loader State
            showSuccessModal: !!flashSuccessMsg,
            successMessage: flashSuccessMsg || '',
            isSubmitting: false,

            closeSuccessModal() {
                this.showSuccessModal = false;
            },

            resetForm() {
                this.customerName = 'Pelanggan Toko Offline';
                this.customerPhone = '080000000000';
                this.customerEmail = '';
                this.orderType = 'pickup';
                this.deliveryAddress = '';
                this.paymentPlan = 'full';
                this.notes = '';
                this.placedAtText = defaultDateStr;
                this.fulfillAtText = defaultDateStr;
                this.items = [{ product_id: '', price: 0, quantity: 1 }];
            },

            addItem() {
                if (this.items.length < this.productList.length) {
                    this.items.push({ product_id: '', price: 0, quantity: 1 });
                } else {
                    alert('Jumlah baris produk sudah mencapai batas maksimum jenis produk yang tersedia.');
                }
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            isProductSelected(productId, currentIndex) {
                return this.items.some((item, idx) => idx !== currentIndex && item.product_id == productId);
            },

            onProductSelect(index) {
                const selectedId = this.items[index].product_id;
                const foundProduct = this.productList.find(p => p.id == selectedId);
                if (foundProduct) {
                    this.items[index].price = parseFloat(foundProduct.harga) || 0;
                }
            },

            calculateSubtotal() {
                return this.items.reduce((sum, item) => {
                    const price = parseFloat(item.price) || 0;
                    const qty = parseInt(item.quantity) || 0;
                    return sum + (price * qty);
                }, 0);
            },

            formatNumber(val) {
                return new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
            },

            formatDateInput(event, field) {
                let v = event.target.value.replace(/\D/g, '');
                if (v.length > 8) v = v.substring(0, 8);
                
                if (v.length >= 5) {
                    this[field] = `${v.substring(0, 2)}/${v.substring(2, 4)}/${v.substring(4)}`;
                } else if (v.length >= 3) {
                    this[field] = `${v.substring(0, 2)}/${v.substring(2)}`;
                } else {
                    this[field] = v;
                }
            },

            convertToDbDate(dateStr) {
                if (!dateStr) return '';
                const parts = dateStr.split('/');
                if (parts.length === 3 && parts[2].length === 4) {
                    const dd = parts[0].padStart(2, '0');
                    const mm = parts[1].padStart(2, '0');
                    const yyyy = parts[2];
                    return `${yyyy}-${mm}-${dd} 12:00:00`;
                }
                return dateStr;
            },

            handleSubmit(e) {
                const dateRegex = /^\d{2}\/\d{2}\/\d{4}$/;
                if (!dateRegex.test(this.placedAtText) || !dateRegex.test(this.fulfillAtText)) {
                    e.preventDefault();
                    alert('Format tanggal wajib dd/mm/yyyy (contoh: 14/08/2026)');
                    return false;
                }

                // Cek duplikasi produk sebelum submit
                const selectedProductIds = this.items.map(item => item.product_id).filter(id => id !== '');
                const hasDuplicates = new Set(selectedProductIds).size !== selectedProductIds.length;
                if (hasDuplicates) {
                    e.preventDefault();
                    alert('Terdapat produk yang dipilih lebih dari satu kali. Silakan gabungkan jumlah/Qty pada satu baris produk.');
                    return false;
                }

                if (this.calculateSubtotal() <= 0) {
                    e.preventDefault();
                    alert('Total belanja tidak boleh Rp 0. Silakan tentukan produk dan jumlahnya.');
                    return false;
                }

                // PAKSA SINKRONISASI VALUE KE TAG <INPUT TYPE="HIDDEN"> NATIVE
                const placedEl = document.getElementById('placed_at_hidden');
                const fulfillEl = document.getElementById('fulfill_at_hidden');
                if (placedEl) placedEl.value = this.convertToDbDate(this.placedAtText);
                if (fulfillEl) fulfillEl.value = this.convertToDbDate(this.fulfillAtText);

                // Aktifkan State Loading Tombol
                this.isSubmitting = true;
            }
        };
    }
</script>
@endsection