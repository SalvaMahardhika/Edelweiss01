<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
