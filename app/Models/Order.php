<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'order_type',
        'status',
        'payment_plan',
        'payment_status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'dp_amount',
        'amount_paid',
        'fulfill_at',
        'settlement_due_at',
        'notes',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_plan' => PaymentPlan::class,
            'payment_status' => PaymentStatus::class,
            'fulfill_at' => 'datetime',
            'settlement_due_at' => 'datetime',
            'placed_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($order) {
            // Order tak boleh completed / diserahkan sebelum isFullyPaid()
            if ($order->status === OrderStatus::Completed && !$order->isFullyPaid()) {
                throw new \DomainException("Order cannot be completed before it is fully paid.");
            }

            // fulfill_at must be in the future
            if ($order->fulfill_at && $order->fulfill_at->lessThanOrEqualTo(Carbon::now())) {
                throw new \DomainException("Fulfill date must be in the future.");
            }

            // dp_amount must be between 10% and 90% of total_amount when using DP plan
            if ($order->payment_plan === PaymentPlan::Dp) {
                $min = bcmul((string) $order->total_amount, '0.10', 2);
                $max = bcmul((string) $order->total_amount, '0.90', 2);
                if (bccomp((string) $order->dp_amount, $min, 2) < 0 || bccomp((string) $order->dp_amount, $max, 2) > 0) {
                    throw new \DomainException("Down‑payment amount must be between 10% and 90% of total amount.");
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helper turunan — sisa tagihan TIDAK disimpan, dihitung
    public function remaining(): string
    {
        return bcsub((string)$this->total_amount, (string)$this->amount_paid, 2);
    }

    public function isFullyPaid(): bool
    {
        return bccomp($this->remaining(), '0.00', 2) <= 0;
    }

    /**
     * Hitung ulang subtotal, tax, total, dan dp_amount berdasarkan harga produk di DB
     */
    public function recalculateTotals()
    {
        $subtotal = '0.00';
        
        // Refresh relation to load newly added items
        if ($this->relationLoaded('items')) {
            $this->unsetRelation('items');
        }

        foreach ($this->items as $item) {
            $product = $item->product ?? Produk::find($item->product_id);
            $price = $product ? $product->harga : '0.00';

            $item->product_name = $product ? $product->nama_produk : $item->product_name;
            $item->unit_price = $price;
            $item->subtotal = bcmul((string)$price, (string)$item->quantity, 2);
            
            if ($item->isDirty()) {
                $item->save();
            }

            $subtotal = bcadd($subtotal, $item->subtotal, 2);
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = bcmul($subtotal, '0.11', 2); // 11% tax
        $this->total_amount = bcadd($subtotal, $this->tax_amount, 2);

        if ($this->payment_plan === PaymentPlan::Dp) {
            $this->dp_amount = bcmul($this->total_amount, '0.50', 2); // 50% DP
        } else {
            $this->dp_amount = '0.00';
        }
    }

    /**
     * Hitung amount_paid dari semua payments yang sukses
     */
    public function recalculatePaymentStatus()
    {
        $this->amount_paid = $this->payments()
            ->whereIn('status', ['settlement', 'paid'])
            ->sum('amount');

        if (bccomp((string)$this->amount_paid, (string)$this->total_amount, 2) >= 0) {
            $this->payment_status = PaymentStatus::Paid;
        } elseif ($this->payment_plan === PaymentPlan::Dp && bccomp((string)$this->amount_paid, (string)$this->dp_amount, 2) >= 0) {
            $this->payment_status = PaymentStatus::Partial;
        } else {
            $this->payment_status = PaymentStatus::Unpaid;
        }

        $this->save();
    }
}
