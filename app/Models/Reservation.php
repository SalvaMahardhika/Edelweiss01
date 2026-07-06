<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'reserved_at',
        'party_size',
        'status',
        'table_number',
        'notes',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'party_size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
