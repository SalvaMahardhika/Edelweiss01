# ADR-0007 · Pre-order & Invariant: Order Tidak Bisa `completed` Sebelum Lunas

- **Status**: Accepted
- **Tanggal**: 2026-07-03
- **Decider**: Tim Edelweiss Bakery

---

## Konteks

Sistem menerima **pre-order** — customer memesan dan membayar (atau DP) jauh sebelum
waktu pengambilan. Order perlu memiliki:

1. Waktu pengambilan yang dijadwalkan (`fulfill_at`).
2. Jaminan bahwa order tidak ditandai `completed` sebelum pembayaran lunas.

## Masalah

Tanpa aturan eksplisit:

- Staf bisa tidak sengaja menandai order sebagai `completed` padahal customer belum lunas —
  produk keluar tanpa pembayaran penuh.
- Tidak ada cara membedakan "order untuk hari ini" vs "pre-order minggu depan" di dasbor admin.
- Tanpa validasi status, sistem bisa masuk ke state yang tidak konsisten:
  `status=completed` + `payment_status=partial` — kontradiksi bisnis.

## Keputusan

**Dua mekanisme diimplementasikan:**

### 1. Kolom `fulfill_at` untuk Pre-order

```php
$table->dateTime('fulfill_at')->nullable(); // waktu pengambilan yang dijadwalkan
```

- Terisi saat customer memilih tanggal/jam pengambilan di checkout.
- Admin dapat menyaring order berdasarkan `fulfill_at` untuk persiapan produksi.
- `null` berarti "segera / walk-in".

### 2. Guard Invariant: Tidak Selesai Sebelum Lunas

Aturan bisnis ini di-enforce di **dua lapis**:

**Lapis 1 — Model (domain logic):**

```php
// app/Models/Order.php
public function markCompleted(): void
{
    if ($this->payment_status !== PaymentStatus::Paid) {
        throw new \DomainException(
            "Order #{$this->order_number} tidak dapat diselesaikan sebelum pembayaran lunas."
        );
    }

    $this->update(['status' => OrderStatus::Completed]);
}
```

**Lapis 2 — Controller (defensive check):**

```php
// Sebelum update status ke completed
abort_if(
    $order->payment_status !== PaymentStatus::Paid,
    422,
    'Pembayaran harus lunas sebelum order dapat diselesaikan.'
);
```

**Lapis 3 — Test (otomatis, CI):**

```php
// tests/Feature/OrderInvariantTest.php
it('cannot complete an order that is not fully paid', function () {
    $order = Order::factory()->create(['payment_status' => 'partial']);

    expect(fn() => $order->markCompleted())
        ->toThrow(\DomainException::class);
});
```

## Konsekuensi

- ✅ **Invariant bisnis terjaga** — tidak ada order `completed` + `payment_status != paid`.
- ✅ **Pre-order terstruktur** — dasbor admin bisa difilter berdasarkan `fulfill_at`.
- ✅ **Pengujian otomatis** — aturan ini di-cover PHPUnit, error terdeteksi di CI sebelum
  deploy.
- ✅ `settlement_due_at` tersedia untuk mengatur batas waktu pelunasan DP.
- ⚠️ Jika ada kebutuhan "completed tapi belum bayar" (misal bayar tunai di tempat + langsung
  selesai), alurnya harus: catat pembayaran cash terlebih dahulu → `payment_status = paid` →
  baru `completed`. Tidak ada jalan pintas melewati guard ini.
