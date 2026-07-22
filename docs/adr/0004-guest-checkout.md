# ADR-0004 · Guest Checkout: `user_id` Nullable + Kontak per-Order

- **Status**: Accepted
- **Tanggal**: 2026-07-03
- **Decider**: Tim Edelweiss Bakery

---

## Konteks

Sistem pemesanan bakeri perlu memutuskan apakah customer **wajib membuat akun** sebelum
bisa memesan, atau boleh memesan sebagai **tamu (guest)**.

## Masalah

**Mewajibkan registrasi menambah friksi yang signifikan:**

- Riset UX menunjukkan bahwa checkout dengan registrasi wajib meningkatkan **cart abandonment
  rate** hingga 26% (sumber: Baymard Institute).
- Untuk bakeri lokal skala UMKM, customer sering memesan lewat WA dan baru diarahkan ke web
  — memaksa mereka daftar akun terasa berlebihan.
- Customer yang hanya ingin memesan sekali tidak mau membuat akun hanya untuk satu transaksi.

## Keputusan

**`user_id` pada tabel `orders` dibuat `nullable`, dan setiap order menyimpan data kontak
mandiri (`customer_name`, `customer_phone`, `customer_email`).**

Skema:

```php
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

$table->string('customer_name');           // wajib
$table->string('customer_phone');          // wajib, untuk konfirmasi WA
$table->string('customer_email')->nullable(); // opsional
```

Alur:
- **Customer terdaftar (login)**: `user_id` terisi, data kontak bisa otomatis diisi dari profil.
- **Guest**: `user_id` = `null`, data kontak diisi manual di form checkout.

Pencarian order bisa dilakukan via `order_number` + `customer_phone` untuk guest.

## Konsekuensi

- ✅ **Mengurangi friksi** — siapapun bisa langsung memesan tanpa hambatan registrasi.
- ✅ **Data kontak tetap tersimpan** — admin tetap bisa menghubungi customer via telepon/WA.
- ✅ Customer terdaftar tetap mendapat benefit (riwayat order, tidak perlu isi ulang data).
- ⚠️ Tidak ada mekanisme otomatis untuk mengirimkan update status order ke guest via email
  (karena email nullable) — perlu konfirmasi manual lewat WhatsApp.
- ⚠️ Data guest tidak bisa digabung dengan akun jika customer kemudian mendaftar — diterima
  sebagai trade-off untuk kesederhanaan.
