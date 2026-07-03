<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: total_amount, dp_amount, & subtotal are always calculated from the product database price, not client-side input.
     */
    public function test_order_totals_calculated_from_database_product_prices()
    {
        $user = User::factory()->create();
        $product1 = Produk::factory()->create([
            'harga' => 100000.00, // Rp100.000
        ]);
        $product2 = Produk::factory()->create([
            'harga' => 50000.00,  // Rp50.000
        ]);

        $orderData = [
            'order_number' => 'EDL-TEST-0001',
            'user_id' => $user->id,
            'customer_name' => 'Hacker Client',
            'customer_phone' => '0812345678',
            'payment_plan' => PaymentPlan::Full,
            // Client attempts to pass manipulated cheap totals
            'subtotal' => 1.00,
            'tax_amount' => 0.00,
            'total_amount' => 1.00,
        ];

        // Items data from client, client might try to pass incorrect prices
        $itemsData = [
            [
                'product_id' => $product1->id,
                'quantity' => 2, // 2 * 100k = 200k
                'unit_price' => 1.00,
                'subtotal' => 2.00,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 1, // 1 * 50k = 50k
                'unit_price' => 1.00,
                'subtotal' => 1.00,
            ]
        ];

        $service = new \App\Services\OrderService();
        $order = $service->createOrder($orderData, $itemsData);

        // Assert server-calculated prices are correct (250k subtotal, 27.5k tax, 277.5k total)
        // Client-side inputs for subtotal, tax_amount, total_amount and item prices were successfully ignored!
        $this->assertEquals(250000.00, (float)$order->subtotal);
        $this->assertEquals(27500.00, (float)$order->tax_amount);
        $this->assertEquals(277500.00, (float)$order->total_amount);

        // Verify order items table has the correct snapshot prices
        $order->load('items');
        $this->assertCount(2, $order->items);
        $this->assertEquals(100000.00, (float)$order->items[0]->unit_price);
        $this->assertEquals(50000.00, (float)$order->items[1]->unit_price);
    }

    /**
     * Test 2: amount_paid = sum of all successful payments (verified by server).
     */
    public function test_amount_paid_is_calculated_from_successful_payments()
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'EDL-TEST-0002',
            'user_id' => $user->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '0812345678',
            'payment_plan' => PaymentPlan::Dp,
            'subtotal' => 100000.00,
            'tax_amount' => 11000.00,
            'total_amount' => 111000.00,
            'dp_amount' => 55500.00,
            'amount_paid' => 0.00,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        // 1. Success down payment of Rp55,500
        $payment1 = Payment::create([
            'order_id' => $order->id,
            'amount' => 55500.00,
            'status' => 'settlement', // Successful status
            'type' => 'down_payment',
        ]);

        $order->refresh();
        $this->assertEquals(55500.00, (float)$order->amount_paid);
        $this->assertEquals(PaymentStatus::Partial, $order->payment_status);

        // 2. Failed payment of Rp55,500 (should not increase amount_paid)
        Payment::create([
            'order_id' => $order->id,
            'amount' => 55500.00,
            'status' => 'failed',
            'type' => 'settlement',
        ]);

        $order->refresh();
        $this->assertEquals(55500.00, (float)$order->amount_paid);

        // 3. Success settlement of the remaining Rp55,500
        $payment2 = Payment::create([
            'order_id' => $order->id,
            'amount' => 55500.00,
            'status' => 'settlement', // Successful status
            'type' => 'settlement',
        ]);

        $order->refresh();
        $this->assertEquals(111000.00, (float)$order->amount_paid);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertTrue($order->isFullyPaid());
    }

    /**
     * Test 3: Order cannot be completed before isFullyPaid() is true.
     */
    public function test_order_cannot_be_completed_before_fully_paid()
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'EDL-TEST-0003',
            'user_id' => $user->id,
            'customer_name' => 'Jane Doe',
            'customer_phone' => '0812345678',
            'payment_plan' => PaymentPlan::Full,
            'status' => OrderStatus::Pending,
            'total_amount' => 100000.00,
            'amount_paid' => 0.00,
        ]);

        // Create the initial 50% payment (Rp50,000)
        Payment::create([
            'order_id' => $order->id,
            'amount' => 50000.00,
            'status' => 'settlement',
            'type' => 'full',
        ]);

        $order->refresh();

        // Attempting to set order status to completed before it is fully paid must throw exception
        try {
            $order->status = OrderStatus::Completed;
            $order->save();
            $this->fail("Expected DomainException to be thrown when completing an unpaid order.");
        } catch (\DomainException $e) {
            $this->assertEquals("Order cannot be completed before it is fully paid.", $e->getMessage());
        }

        // Verify order status remains pending in database
        $order->refresh();
        $this->assertEquals(OrderStatus::Pending, $order->status);

        // Success payment to make it fully paid
        Payment::create([
            'order_id' => $order->id,
            'amount' => 50000.00,
            'status' => 'settlement',
            'type' => 'full',
        ]);

        $order->refresh();
        $this->assertTrue($order->isFullyPaid());

        // Attempting to complete the order now should succeed
        $order->status = OrderStatus::Completed;
        $order->save();

        $this->assertEquals(OrderStatus::Completed, $order->status);
    }
}
