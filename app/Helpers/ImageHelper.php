<?php

namespace App\Helpers;

use Exception;

class ImageHelper
{
    /**
     * Otomatis convert gambar ke WebP dan compress ukurannya menggunakan GD.
     *
     * @param  string  $sourcePath  Path lengkap file gambar asal.
     * @param  string  $destinationPath  Path lengkap tujuan file .webp disimpan.
     * @param  int  $quality  Tingkat kualitas gambar WebP (1-100), default 75 (rekomendasi Google).
     * @param  int|null  $maxWidth  Opsional, batas lebar maksimum jika ingin resize otomatis.
     *
     * @throws Exception
     */
    public static function convertToWebp(string $sourcePath, string $destinationPath, int $quality = 75, ?int $maxWidth = 1200): bool
    {
        if (! file_exists($sourcePath)) {
            throw new Exception("File gambar asal tidak ditemukan: {$sourcePath}");
        }

        // 1. Dapatkan informasi jenis ekstensi gambar
        $imageInfo = getimagesize($sourcePath);
        if (! $imageInfo) {
            throw new Exception('File bukan merupakan gambar yang valid.');
        }

        $mimeType = $imageInfo['mime'];

        // 2. Buat resource gambar GD berdasarkan Mime Type asal
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                // Jaga transparansi PNG agar tidak berubah jadi hitam saat diproses GD
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                imagepalettetotruecolor($image);
                break;
            default:
                throw new Exception("Format gambar tidak didukung oleh GD: {$mimeType}");
        }

        if (! $image) {
            throw new Exception('Gagal memproses resource gambar.');
        }

        // 3. Fitur Auto-Resize jika gambar terlalu besar (Opsional)
        if ($maxWidth && $imageInfo[0] > $maxWidth) {
            $origWidth = $imageInfo[0];
            $origHeight = $imageInfo[1];

            // Hitung rasio tinggi proporsional agar gambar tidak gepeng
            $newWidth = $maxWidth;
            $newHeight = (int) (($origHeight / $origWidth) * $maxWidth);

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Pertahankan transparansi pada proses resize
            if ($mimeType === 'image/png') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
            }

            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            // Tukar resource gambar lama dengan yang sudah di-resize
            imagedestroy($image);
            $image = $resizedImage;
        }

        // 4. Pastikan folder tujuan sudah ada
        $dir = dirname($destinationPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 5. Simpan dan compress menjadi format WebP
        $saveStatus = imagewebp($image, $destinationPath, $quality);

        // 6. Bersihkan memory resource GD
        imagedestroy($image);

        return $saveStatus;
    }
}
