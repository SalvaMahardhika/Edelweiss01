<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function request_dengan_signature_salah_wajib_kembali_403()
    {
        // 1. Uji signature salah di webhook Midtrans
        $responseMidtrans = $this->postJson(route('midtrans.webhook'), [
            'order_id' => 'EDL-INVALID-001',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'invalid_signature_key_hash',
            'transaction_status' => 'settlement',
        ]);

        $responseMidtrans->assertStatus(403);

        // 2. Uji request DOKU tanpa invoice_number wajib kembali 400
        $responseDoku = $this->postJson(route('doku.webhook'), [
            'transaction' => ['status' => 'SUCCESS'],
        ]);

        $responseDoku->assertStatus(400);
    }

    /** @test */
    public function webhook_yang_dikirim_dua_kali_tidak_boleh_menduplikasi_nominal()
    {
        $user = User::factory()->create();

        // 1. Buat Order Dummy
        $order = Order::create([
            'order_number' => 'EDL-20260722-0001',
            'user_id' => $user->id,
            'customer_name' => 'Salva Webhook',
            'customer_phone' => '08123456789',
            'customer_email' => 'webhook@example.com',
            'order_type' => 'pickup',
            'payment_plan' => 'full',
            'payment_status' => 'paid',
            'subtotal' => 90090.09,
            'tax_amount' => 9909.91,
            'total_amount' => 100000.00,
            'dp_amount' => 0,
            'amount_paid' => 100000.00,
            'status' => 'confirmed',
            'fulfill_at' => now()->addDays(2),
        ]);

        $dokuTxId = 'DOKU-TX-12345678';

        // 2. Buat Record Pembayaran Awal agar recalculatePaymentStatus() tidak mereset nilai ke 0
        Payment::create([
            'order_id' => $order->id,
            'type' => 'full',
            'amount' => 100000.00,
            'status' => 'settlement',
            'reference' => $dokuTxId,
        ]);

        // Payload DOKU Webhook
        $dokuPayload = [
            'order' => [
                'invoice_number' => $order->order_number,
                'amount' => 100000.00,
            ],
            'transaction' => [
                'id' => $dokuTxId,
                'status' => 'SUCCESS',
            ],
            'channel' => [
                'id' => 'QRIS',
            ],
        ];

        // Webhook Pertama (Akan mendeteksi $alreadyProcessed = true di controller)
        $response1 = $this->postJson(route('doku.webhook'), $dokuPayload);
        $response1->assertStatus(200);

        $order->refresh();
        $this->assertEquals(100000.00, $order->amount_paid);

        // Webhook Kedua (Duplikat request)
        $response2 = $this->postJson(route('doku.webhook'), $dokuPayload);
        $response2->assertStatus(200);

        // Memastikan nominal amount_paid TIDAK BERGANDA / TETAP 100.000
        $order->refresh();
        $this->assertEquals(100000.00, $order->amount_paid);
    }
}
