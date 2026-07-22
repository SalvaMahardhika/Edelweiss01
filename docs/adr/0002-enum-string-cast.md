# ADR-0002 · Enum sebagai `string` + PHP Enum Cast

- **Status**: Accepted
- **Tanggal**: 2026-07-03
- **Decider**: Tim Edelweiss Bakery

---

## Konteks

Banyak kolom di sistem membutuhkan nilai terbatas yang terdefinisi:
`status` order, `payment_status`, `payment_plan`, `type` pembayaran, dsb.

Ada dua pendekatan utama:

1. **`ENUM` MySQL native** — tipe kolom ENUM langsung di database.
2. **`string` + PHP Enum** — kolom `string` di database, dipetakan ke PHP `enum` via Eloquent cast.

## Masalah

**`ENUM` MySQL native memiliki keterbatasan serius:**

- Mengubah nilai ENUM membutuhkan `ALTER TABLE` yang bisa mengunci tabel besar.
- Tidak ada type-safety di level PHP tanpa lapisan tambahan.
- Migrasi ke database lain (PostgreSQL, SQLite untuk testing) sering bermasalah karena
  ENUM tidak standar antar vendor.
- Tidak bisa digunakan langsung sebagai PHP object/constant.

## Keputusan

**Kolom status dan tipe disimpan sebagai `string` di database, lalu di-cast ke PHP `enum`
(backed enum) di level Model Eloquent.**

Contoh implementasi:

```php
// app/Enums/OrderStatus.php
enum OrderStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case Preparing  = 'preparing';
    case Ready      = 'ready';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
}

// app/Models/Order.php
protected $casts = [
    'status'         => OrderStatus::class,
    'payment_status' => PaymentStatus::class,
    'payment_plan'   => PaymentPlan::class,
];
```

Kolom migrasi tetap `string`:

```php
$table->string('status')->default('pending');
$table->string('payment_status')->default('unpaid');
$table->string('payment_plan')->default('full');
```

## Konsekuensi

- ✅ **Type-safe** — IDE dan PHPStan menangkap nilai yang tidak valid saat compile time.
- ✅ **Fleksibel** — menambah nilai baru cukup tambah case di PHP enum + migrasi default sederhana.
- ✅ **Kompatibel** dengan SQLite (digunakan untuk PHPUnit testing).
- ✅ **Readable** — `$order->status === OrderStatus::Completed` lebih ekspresif dari string mentah.
- ⚠️ Nilai tidak valid yang masuk dari luar (misal API lama) akan melempar `ValueError` — perlu
  validasi di Request.
