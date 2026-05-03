<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'nama_produk',
        'gambar',
        'harga',
        'deskripsi',
        'status',
        'id_user'
    ];

    public $timestamps = true;
}