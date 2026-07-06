<?php

namespace App\Models;

use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'type',
        'provider',
        'method',
        'amount',
        'currency',
        'status',
        'reference',
        'snap_token',
        'payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'amount' => 'decimal:2',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::saved(function ($payment) {
            $order = $payment->order;
            if ($order) {
                $order->recalculatePaymentStatus();
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
