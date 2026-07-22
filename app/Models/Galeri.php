<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    use HasFactory;

    protected $table = 'galeri';

    protected $fillable = [
        'judul',
        'album',
        'deskripsi',
        'user_id',
    ];

    // ================= RELATION USER =================
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
