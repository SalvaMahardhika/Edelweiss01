# Edelweiss Bakery — Pre-Order & Production Management System

Sistem informasi pemesanan (*pre-order*) dan manajemen produksi berbasis web untuk **Edelweiss Bakery**. Aplikasi ini menggantikan pencatatan pesanan manual dengan sistem katalog dinamis, *guest & member checkout*, validasi tanggal produksi (*lock date*), skema pembayaran uang muka (DP 50%) dan pelunasan otomatis via *Payment Gateway*, verifikasi transfer manual, notifikasi Telegram *real-time*, serta rekapitulasi laporan penjualan.

---

## Tech Stack

* **Backend Framework:** Laravel 12
* **PHP Version:** PHP 8.5+
* **Database:** MySQL / MariaDB
* **Frontend:** Blade Template, JavaScript, Responsive CSS
* **Integrasi Eksternal:**
  * Payment Gateway API (Midtrans / DOKU)
  * Telegram Bot API (Notifikasi Pesanan Baru & Log Error)
  * WhatsApp Redirect (Konfirmasi Transfer Manual & Pengajuan Pembatalan)

---

## Persyaratan Server & Konfigurasi `php.ini`

Sebelum menjalankan aplikasi di lingkungan lokal maupun *shared hosting* (cPanel), pastikan versi PHP dan ekstensi berikut telah diaktifkan pada berkas `php.ini`:

### 1. Versi Minimum
* **PHP:** `>= 8.5`
* **Composer:** `>= 2.x`
* **Node.js & NPM:** `>= 20.x` (untuk kompilasi aset *frontend*)
* **MySQL:** `>= 8.0` atau **MariaDB** `>= 10.6`

### 2. Ekstensi PHP Wajib (`php.ini`)
Pastikan baris berikut tidak dikomentari (hapus tanda titik koma `;` di depannya) pada `php.ini` atau aktifkan melalui menu **Select PHP Version** di cPanel:

```ini
extension=bcmath      ; Wajib untuk kalkulasi presisi tinggi harga, pajak, dan DP (bcadd/bcmul)
extension=gd          ; Wajib untuk kompresi & pemrosesan gambar (ImageHelper)
extension=zip         ; Wajib untuk fitur ekspor/unduh Laporan Penjualan ke format Excel (.xlsx)
extension=xml         ; Wajib untuk pemrosesan struktur dokumen spreadsheet/Excel
extension=mbstring    ; Wajib untuk penanganan string multibyte Laravel
extension=openssl     ; Wajib untuk enkripsi & komunikasi HTTPS ke API Payment Gateway
extension=pdo_mysql   ; Wajib untuk koneksi basis data MySQL
extension=curl        ; Wajib untuk request HTTP ke API Midtrans, DOKU, dan Telegram Bot
extension=fileinfo    ; Wajib untuk validasi MIME type unggahan bukti transfer & foto produk