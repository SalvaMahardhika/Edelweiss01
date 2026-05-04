<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // ================= ACCESSOR GAMBAR =================
    public function getGambarUrlAttribute()
    {
        return asset('storage/' . $this->gambar);
    }

    // ================= SCOPE =================
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}