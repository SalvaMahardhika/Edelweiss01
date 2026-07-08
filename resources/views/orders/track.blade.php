<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan #{{ $order->order_number }} - Edelweiss Bakery</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6 text-center">
            <h1 class="text-xl font-bold text-slate-900">Status Pesanan Edelweiss Bakery</h1>
            <p class="text-slate-500 mt-1">Nomor Nota: <span class="font-mono font-bold text-indigo-600">{{ $order->order_number }}</span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-md font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">💵 Ringkasan Pembayaran</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Tagihan:</span>
                            <span class="font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-600">
                            <span>Sudah Terbayar:</span>
                            <span class="font-semibold">- Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t border-dashed border-slate-200 pt-3 text-base font-bold">
                            <span class="text-slate-800">Sisa Tagihan:</span>
                            <span class="text-rose-600">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        {{-- 🔑 KONDISI 1: Jika masih ada sisa tagihan, paksa munculkan tombol pelunasan --}}
                        @if($remainingAmount > 0)
                            <div class="p-4 bg-amber-50 rounded-xl text-amber-800 text-sm mb-4 border border-amber-100">
                                ⚠️ Pembayaran Anda baru mencakup uang muka (DP). Silakan lunasi sisa tagihan sebesar **Rp {{ number_format($remainingAmount, 0, ',', '.') }}** untuk memproses pesanan ke tahap produksi berikutnya.
                            </div>
                            @if($snapToken)
                                <button id="pay-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition cursor-pointer text-center block">
                                    💳 Lunasi Sisa Tagihan Sekarang
                                </button>
                            @endif
                        {{-- 🔑 KONDISI 2: Jika sisa tagihan sudah 0 (Lunas murni) --}}
                        @else
                            <div class="p-4 bg-emerald-50 rounded-xl text-emerald-800 text-sm border border-emerald-100">
                                <p class="font-bold mb-1">🎉 Pesanan Sudah Lunas!</p>
                                <p>Silakan ambil kue pesanan segar Anda di outlet Edelweiss Bakery pada koordinat waktu:</p>
                                <p class="mt-2 font-mono font-bold text-slate-900 bg-white inline-block px-3 py-1 rounded-md border border-emerald-200">
                                    📅 {{ \Carbon\Carbon::parse($order->fulfill_at)->translatedFormat('d F Y (H:i') }} WIB)
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 h-fit">
                <h2 class="text-md font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">⏱️ Timeline Status</h2>
                <div class="space-y-4 text-sm">
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-indigo-600"></div>
                            <div class="w-0.5 h-12 bg-slate-200"></div>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 capitalize">{{ $order->status instanceof \BackedEnum ? $order->status->value : $order->status }}</p>
                            <p class="text-xs text-slate-400">Update Terakhir</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-3 h-3 rounded-full bg-slate-300 mt-1"></div>
                        <div>
                            <p class="font-medium text-slate-400">Pesanan Diproses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($snapToken && $remainingAmount > 0)
    <script type="text/javascript">
        const payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.onclick = function () {
                window.snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result){
                        alert("Pembayaran berhasil diproses!");
                        window.location.reload();
                    },
                    onPending: function(result){
                        alert("Menunggu pembayaran Anda.");
                    },
                    onError: function(result){
                        alert("Pembayaran gagal, silakan coba kembali.");
                    }
                });
            };
        }
    </script>
    @endif
</body>
</html>