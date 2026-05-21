<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\CryptoHelper; // Pastikan Helper yang kita buat kemarin sudah ada

class Produk extends Model
{
    // ================= TABLE =================
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    public $timestamps = true;

    // ================= FILLABLE =================
    protected $fillable = [
        'nama_produk',
        'gambar',
        'harga',
        'deskripsi',
        'status',
        'id_user'
    ];

    // ================= CAST =================
    protected $casts = [
        'harga' => 'integer',
        'status' => 'boolean'
    ];

    // ================= RELATION =================
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // ================= ACCESSOR =================
    public function getGambarUrlAttribute()
    {
        return asset('storage/' . $this->gambar);
    }

    /**
     * ACCESSOR BARU: Mengubah id_produk asli menjadi string terenkripsi AES-256-CBC
     * Panggil di Blade menggunakan: $produk->encrypted_id
     */
    public function getEncryptedIdAttribute()
    {
        return CryptoHelper::encryptId($this->attributes['id_produk']);
    }

    // ================= SCOPE =================
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}