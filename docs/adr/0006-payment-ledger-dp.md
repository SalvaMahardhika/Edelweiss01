# ADR-0006 · Pembayaran Bertahap (DP): Ledger `payments` + `amount_paid`

- **Status**: Accepted
- **Tanggal**: 2026-07-03
- **Decider**: Tim Edelweiss Bakery

---

## Konteks

Bakeri menerima dua skema pembayaran:

1. **Lunas (full)** — customer membayar seluruh total saat memesan.
2. **DP (down payment)** — customer membayar sebagian di awal (misal 50%), sisanya
   dilunasi sebelum/saat pengambilan.

Perlu diputuskan bagaimana mencatat transaksi pembayaran ini di database.

## Masalah

Pendekatan sederhana (satu kolom `paid_amount` di tabel `orders`) tidak cukup:

- Tidak ada jejak audit (audit trail) untuk setiap transaksi yang terjadi.
- Sulit membedakan "DP pertama" vs "pelunasan kedua" untuk rekonsiliasi dengan Midtrans.
- Jika pembayaran gagal di tengah jalan, tidak ada cara merekam percobaan pembayaran yang gagal.
- Tidak bisa mendukung pembayaran melalui beberapa channel (misal DP via QRIS, lunas via
  transfer bank).

## Keputusan

**Setiap transaksi pembayaran dicatat sebagai satu baris di tabel `payments` (model ledger).
Kolom `orders.amount_paid` adalah akumulasi dari semua pembayaran `settlement`/`paid`.
Sisa tagihan dihitung sebagai derivasi: `remaining = total_amount - amount_paid`.**

Skema tabel `payments`:

```php
$table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
$table->string('type')->default('full');     // down_payment | settlement | full
$table->string('provider')->default('midtrans');
$table->string('method')->nullable();        // qris | gopay | bank_transfer
$table->decimal('amount', 12, 2);
$table->string('status')->default('pending'); // pending|settlement|paid|failed|expired|refunded
$table->string('reference')->nullable();     // ID transaksi gateway (unik, cegah callback dobel)
$table->string('snap_token')->nullable();    // Midtrans Snap token
$table->json('payload')->nullable();         // raw callback dari gateway
$table->timestamp('paid_at')->nullable();
```

Alur untuk DP:

```
Order dibuat
  → payment type=down_payment, status=pending
  → customer bayar DP via Midtrans
  → callback: payment.status=settlement, orders.amount_paid += dp_amount
  → orders.payment_status = 'partial'

Pelunasan sebelum ambil:
  → payment type=settlement, status=pending
  → customer bayar sisa via Midtrans
  → callback: payment.status=settlement, orders.amount_paid += sisa
  → orders.payment_status = 'paid'
```

Kolom `reference` yang unik mencegah callback Midtrans yang dikirim dua kali
(idempotency) memproses pembayaran dobel.

## Konsekuensi

- ✅ **Audit trail lengkap** — setiap transaksi terekam terpisah, bisa ditelusuri per
  transaksi.
- ✅ **Idempoten** — kolom `reference` unik mencegah double-credit dari callback duplikat.
- ✅ **Fleksibel** — mendukung partial payment, refund sebagian, atau pembayaran dari
  beberapa channel.
- ✅ `payload` JSON menyimpan raw response gateway — berguna untuk dispute/chargeback.
- ⚠️ `orders.amount_paid` harus selalu disinkronkan dengan baris `payments` — ada risiko
  inkonsistensi jika update tidak atomic. Mitigasi: gunakan database transaction.
- ⚠️ `remaining` tidak disimpan di database — dihitung saat dibutuhkan (`total_amount -
  amount_paid`). Pastikan tidak ada kalkulasi "sisa" yang di-hardcode.
