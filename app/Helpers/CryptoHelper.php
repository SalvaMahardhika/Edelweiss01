<?php

namespace App\Helpers;

class CryptoHelper
{
    private static function getKey(): string
    {
        // Kunci 256-bit dari .env
        return base64_decode(env('URL_ENCRYPT_KEY'));
    }

    /**
     * Enkripsi ID menggunakan AES-256-CBC
     */
    public static function encryptId($id): string
    {
        $key = self::getKey();
        $iv  = random_bytes(16); // IV acak 128-bit tiap enkripsi

        $encrypted = openssl_encrypt(
            (string)$id,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Gabung IV + ciphertext, lalu encode ke base64url
        $combined = $iv . $encrypted;
        return rtrim(strtr(base64_encode($combined), '+/', '-_'), '=');
    }

    /**
     * Dekripsi ID dari URL kembali ke integer
     */
    public static function decryptId(string $cipherText): int
    {
        try {
            $key      = self::getKey();
            $combined = base64_decode(strtr($cipherText, '-_', '+/') . '==');

            // Pisahkan IV (16 byte pertama) dan ciphertext
            $iv         = substr($combined, 0, 16);
            $encrypted  = substr($combined, 16);

            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            $realId = (int)$decrypted;

            if ($realId <= 0) abort(404);

            return $realId;

        } catch (\Exception $e) {
            abort(404);
        }
    }
}