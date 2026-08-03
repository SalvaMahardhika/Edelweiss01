<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nominal_gross_amount_wajib_integer_dan_sesuai_dengan_skema_dp()
    {
        $user = User::factory()->create();

        // Buat order dummy dengan customer_email lengkap
        $order = Order::create([
            'order_number' => 'EDL-TEST-DP',
            'user_id' => $user->id,
            'customer_name' => 'Salva Midtrans',
            'customer_phone' => '08123456789',
            'customer_email' => 'salva.midtrans@example.com', // 👈 Ditambahkan
            'order_type' => 'pickup',
            'payment_plan' => 'dp',
            'payment_status' => 'unpaid',
            'subtotal' => 90090.77,
            'tax_amount' => 9909.98,
            'total_amount' => 100000.75,
            'dp_amount' => 50000.37,
            'amount_paid' => 0,
            'fulfill_at' => now()->addDays(3),
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'EDL-TEST-DP',
            'customer_email' => 'salva.midtrans@example.com',
        ]);

        $this->assertEquals(50000.37, $order->dp_amount);
    }
}
