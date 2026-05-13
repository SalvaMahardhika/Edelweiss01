<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    protected $table = 'galeri';

    protected $primaryKey = 'id_galeri';

    protected $fillable = [
        'judul',
        'album',
        'deskripsi',
        'id_user'
    ];

    // ================= RELATION USER =================
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // ================= AUTO TIMESTAMP =================
    public $timestamps = true;
}