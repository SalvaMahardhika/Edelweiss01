# ADR-0003 · Snapshot pada `order_items`

- **Status**: Accepted
- **Tanggal**: 2026-07-03
- **Decider**: Tim Edelweiss Bakery

---

## Konteks

Tabel `order_items` perlu mencatat produk apa saja yang dibeli dalam suatu order, beserta
harga dan kuantitasnya. Ada dua pendekatan untuk menyimpan data ini:

1. **Relasi murni** — hanya simpan `product_id` dan `quantity`, baca detail produk via JOIN.
2. **Snapshot / denormalisasi** — salin `product_name`, `unit_price` (dan data relevan lain)
   ke baris `order_items` saat order dibuat.

## Masalah

Jika `order_items` hanya menyimpan `product_id`:

- Admin **mengubah harga** produk → seluruh order lama yang belum selesai tiba-tiba menampilkan
  harga baru yang salah.
- Admin **menghapus** produk (soft delete) → query JOIN mengembalikan `null`, riwayat pesanan
  rusak.
- **Laporan keuangan historis menjadi tidak akurat** — tidak bisa diaudit.

Ini adalah masalah klasik **mutable reference** dalam sistem e-commerce.

## Keputusan

**Denormalisasi sengaja (intentional denormalization): salin data produk ke `order_items`
pada saat order dibuat.**

Skema tabel:

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->foreignId('product_id')->nullable()->constrained('produk')->nullOnDelete();

    $table->string('product_name');       // snapshot nama saat order
    $table->decimal('unit_price', 12, 2); // snapshot harga saat order
    $table->unsignedInteger('quantity');
    $table->decimal('subtotal', 12, 2);
    $table->string('notes')->nullable();

    $table->timestamps();
});
```

`product_id` dibuat **nullable** (`nullOnDelete`) — jika produk dihapus permanen,
foreign key menjadi `null` tapi `product_name` dan `unit_price` tetap tersimpan.

## Konsekuensi

- ✅ Riwayat order **immutable** — tidak terpengaruh perubahan harga atau penghapusan produk.
- ✅ Laporan keuangan historis akurat dan dapat diaudit.
- ✅ Query lebih sederhana — tidak perlu JOIN kompleks untuk menampilkan detail order.
- ⚠️ Duplikasi data: nama dan harga produk tersimpan di dua tempat. Ini **disengaja** dan
  merupakan trade-off yang diterima.
- ⚠️ Jika ada koreksi harga (misal typo), order yang sudah dibuat tidak otomatis terkoreksi —
  harus dibatalkan dan dibuat ulang.
