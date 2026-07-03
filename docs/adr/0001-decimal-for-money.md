# ADR-0001 · Tipe Data Uang: `decimal(12,2)`

- **Status**: Accepted
- **Tanggal**: 2026-07-03
- **Decider**: Tim Edelweiss Bakery

---

## Konteks

Sistem membutuhkan penyimpanan nilai uang (harga produk, total order, jumlah pembayaran, dll.).
Pilihan tipe data yang tersedia di MySQL antara lain: `FLOAT`, `DOUBLE`, dan `DECIMAL`.

## Masalah

`FLOAT` dan `DOUBLE` menggunakan representasi biner (IEEE 754), yang **tidak dapat merepresentasikan
pecahan desimal secara pasti**. Contoh:

```php
// PHP / floating point
0.1 + 0.2 === 0.3  // false! hasilnya 0.30000000000000004
```

Dalam konteks keuangan, error pembulatan sekecil apapun (Rp 0,01) dapat terakumulasi
menjadi selisih signifikan di laporan keuangan, audit, dan rekonsiliasi pembayaran.

## Keputusan

**Semua kolom yang menyimpan nilai uang menggunakan `decimal(12,2)`.**

Contoh implementasi di migrasi:

```php
$table->decimal('harga',        10, 2);
$table->decimal('subtotal',     12, 2)->default(0);
$table->decimal('tax_amount',   12, 2)->default(0);
$table->decimal('total_amount', 12, 2)->default(0);
$table->decimal('dp_amount',    12, 2)->default(0);
$table->decimal('amount_paid',  12, 2)->default(0);
$table->decimal('amount',       12, 2); // payments
```

`decimal(12,2)` mendukung nilai hingga **9.999.999.999,99** — lebih dari cukup untuk nominal
dalam Rupiah.

## Konsekuensi

- ✅ Tidak ada error pembulatan pada operasi aritmatika uang.
- ✅ Laporan keuangan akurat dan dapat diaudit.
- ✅ Kompatibel langsung dengan format Midtrans (nilai dalam Rupiah bulat).
- ⚠️ Operasi `DECIMAL` sedikit lebih lambat dari `FLOAT` di level database — tidak signifikan
  untuk volume transaksi bakeri skala UMKM.
