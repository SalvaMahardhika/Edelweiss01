<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderInvariantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_prevents_completing_order_before_fully_paid()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Order cannot be completed before it is fully paid.');

        $user = User::factory()->create();

        Order::create([
            'order_number' => 'EDL-'.now()->format('Ymd').'-0001',
            'user_id' => $user->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'test@example.com',
            'order_type' => 'pickup',
            'status' => OrderStatus::Completed,
            'payment_plan' => PaymentPlan::Full,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => '1000.00',
            'tax_amount' => '110.00',
            'total_amount' => '1110.00',
            'dp_amount' => '0.00',
            'amount_paid' => '0.00',
            'fulfill_at' => Carbon::now()->addDay(),
            'settlement_due_at' => Carbon::now()->addDay(),
        ]);
    }

    /** @test */
    public function it_requires_fulfill_at_to_be_in_the_future()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Fulfill date must be in the future.');

        $user = User::factory()->create();

        Order::create([
            'order_number' => 'EDL-'.now()->format('Ymd').'-0002',
            'user_id' => $user->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'test@example.com',
            'order_type' => 'delivery',
            'status' => OrderStatus::Pending,
            'payment_plan' => PaymentPlan::Full,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => '500.00',
            'tax_amount' => '55.00',
            'total_amount' => '555.00',
            'dp_amount' => '0.00',
            'amount_paid' => '0.00',
            'fulfill_at' => Carbon::now()->subDay(),
            'settlement_due_at' => Carbon::now()->addDay(),
        ]);
    }

    /** @test */
    public function it_enforces_dp_amount_range_when_using_dp_plan()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Down‑payment amount must be between 10% and 90% of total amount.');

        $user = User::factory()->create();

        Order::create([
            'order_number' => 'EDL-'.now()->format('Ymd').'-0003',
            'user_id' => $user->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'test@example.com',
            'order_type' => 'pickup',
            'status' => OrderStatus::Pending,
            'payment_plan' => PaymentPlan::Dp,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => '1000.00',
            'tax_amount' => '110.00',
            'total_amount' => '1110.00',
            // 5% of total (invalid, below 10%)
            'dp_amount' => '55.00',
            'amount_paid' => '0.00',
            'fulfill_at' => Carbon::now()->addDay(),
            'settlement_due_at' => Carbon::now()->addDay(),
        ]);
    }
}
