# ADR-0005 · Soft Delete untuk Produk

- **Status**: Accepted
- **Tanggal**: 2026-07-03
- **Decider**: Tim Edelweiss Bakery

---

## Konteks

Admin perlu bisa "menghapus" produk dari katalog. Namun produk yang sudah pernah dipesan
memiliki relasi dengan tabel `order_items`. Ada dua pendekatan:

1. **Hard delete** — baris dihapus permanen dari database.
2. **Soft delete** — baris ditandai `deleted_at`, tidak benar-benar dihapus.

## Masalah

**Hard delete merusak integritas riwayat penjualan:**

- Jika produk dihapus permanen dan `order_items.product_id` tidak nullable, terjadi
  **foreign key violation**.
- Jika dibuat nullable dan produk dihapus, `order_items` kehilangan referensi ke produk —
  laporan "produk terlaris" dan audit tidak bisa dilakukan.
- Admin yang tidak sengaja menghapus produk tidak bisa membatalkan tindakan tersebut.

## Keputusan

**Tabel `produk` menggunakan Laravel Soft Deletes (`deleted_at` timestamp).**
**Foreign key `order_items.product_id` menggunakan `nullOnDelete()`.**

Implementasi:

```php
// Migration produk
$table->softDeletes(); // tambah kolom deleted_at

// Migration order_items
$table->foreignId('product_id')
      ->nullable()
      ->constrained('produk')
      ->nullOnDelete(); // jika produk dihapus permanen (forceDelete), set null

// Model Produk
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;
}
```

**Kombinasi strategi:**
- **Soft delete** (`delete()`) → produk hilang dari katalog, tapi baris tetap ada. `product_id`
  di `order_items` tetap valid.
- **Snapshot** (ADR-0003) → `product_name` dan `unit_price` sudah tersalin, riwayat aman.
- **Force delete** (jika benar-benar diperlukan) → `product_id` menjadi `null`, tapi nama
  dan harga tetap ada dari snapshot.

## Konsekuensi

- ✅ Riwayat penjualan **tidak rusak** saat produk dihapus.
- ✅ Admin bisa **memulihkan** produk yang tidak sengaja dihapus via `restore()`.
- ✅ Laporan historis (produk terlaris, pendapatan per produk) tetap akurat.
- ✅ Produk tidak aktif bisa ditandai `status = false` (sembunyi dari katalog) tanpa dihapus.
- ⚠️ Baris "dihapus" tetap ada di database — perlu pembersihan berkala dengan `forceDelete()`
  untuk produk yang sudah benar-benar tidak dipakai.
- ⚠️ Query yang lupa scope `withTrashed()` tidak akan menemukan produk yang di-soft-delete —
  perlu perhatian saat debugging.
