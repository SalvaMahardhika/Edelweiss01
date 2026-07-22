<?php

namespace App\Models;

use App\Helpers\CryptoHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Pastikan Helper yang kita buat kemarin sudah ada

class Produk extends Model
{
    use HasFactory;

    // ================= TABLE =================
    protected $table = 'produk';

    // ================= FILLABLE =================
    protected $fillable = [
        'category_id',
        'user_id',
        'nama_produk',
        'slug',
        'gambar',
        'harga',
        'deskripsi',
        'status',
        'is_available',
        'is_featured',
    ];

    // ================= CAST =================
    protected $casts = [
        'harga' => 'decimal:2',
        'status' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // ================= RELATION =================
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // ================= ACCESSOR =================
    public function getGambarUrlAttribute()
    {
        return asset('storage/'.$this->gambar);
    }

    /**
     * ACCESSOR BARU: Mengubah id asli menjadi string terenkripsi AES-256-CBC
     * Panggil di Blade menggunakan: $produk->encrypted_id
     */
    public function getEncryptedIdAttribute()
    {
        return CryptoHelper::encryptId($this->attributes['id']);
    }

    // ================= SCOPE =================
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}
