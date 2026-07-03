<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Galeri extends Model
{
    use HasFactory;
    protected $table = 'galeri';

    protected $fillable = [
        'judul',
        'album',
        'deskripsi',
        'user_id'
    ];

    // ================= RELATION USER =================
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}