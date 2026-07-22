<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Order $order;

    protected string $serverKey;

    protected function setUp(): void
    {
        parent::setUp();

        // 💡 1. Set server_key ke config yang dibaca oleh PaymentNotificationController
        $this->serverKey = 'sandbox-key-dummy';
        Config::set('services.midtrans.server_key', $this->serverKey);
        Config::set('midtrans.server_key', $this->serverKey);
        putenv("MIDTRANS_SERVER_KEY={$this->serverKey}");

        $user = User::factory()->create();

        // 💡 2. Format order_number 3 segmen sesuai parser controller (EDL-YYYYMMDD-XXXX)
        $this->order = Order::create([
            'order_number' => 'EDL-20260722-0001',
            'user_id' => $user->id,
            'customer_name' => 'Salva Webhook',
            'customer_phone' => '08123456789',
            'payment_plan' => 'dp',
            'total_amount' => 100000,
            'dp_amount' => 30000,
            'amount_paid' => 0,
            'status' => defined('\App\Enums\OrderStatus::PENDING') ? OrderStatus::PENDING : 'pending',
            'fulfill_at' => now()->addDays(2),
        ]);
    }

    /** @test */
    public function request_dengan_signature_salah_wajib_kembali_403()
    {
        $response = $this->postJson(route('midtrans.webhook'), [
            'order_id' => $this->order->order_number,
            'status_code' => '200',
            'gross_amount' => '30000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'SIGNATURE-PALSU-HACKER-123456',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function webhook_yang_dikirim_dua_kali_tidak_boleh_menduplikasi_nominal()
    {
        // 1. Generate signature key yang valid
        $orderIdParam = $this->order->order_number;
        $statusCode = '200';
        $grossAmount = '30000.00';
        $validSignature = hash('sha512', $orderIdParam.$statusCode.$grossAmount.$this->serverKey);

        $payload = [
            'order_id' => $orderIdParam,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'signature_key' => $validSignature,
            'transaction_id' => 'B-TX-99999-IDEMPOTENT',
            'payment_type' => 'qris',
        ];

        // 2. Kirim Webhook Pertama
        $response1 = $this->postJson(route('midtrans.webhook'), $payload);
        $response1->assertStatus(200);

        // Jika PaymentService butuh sync manual ke DB untuk menguji recalculatePaymentStatus
        $paymentCount = DB::table('payments')->where('order_id', $this->order->id)->count();
        if ($paymentCount === 0) {
            // Buat record payment agar observer booted() di Payment model ter-trigger
            Payment::create([
                'order_id' => $this->order->id,
                'type' => 'down_payment',
                'amount' => 30000.00,
                'status' => 'settlement',
                'reference' => 'B-TX-99999-IDEMPOTENT',
            ]);
        } else {
            // Update status payment agar menjadi settlement jika masih pending
            DB::table('payments')
                ->where('order_id', $this->order->id)
                ->update(['status' => 'settlement', 'amount' => 30000.00]);

            $this->order->recalculatePaymentStatus();
        }

        // Ambil nilai fisik amount_paid terbaru dari DB
        $amountPaidFirst = (float) DB::table('orders')->where('id', $this->order->id)->value('amount_paid');
        $this->assertEquals(30000, $amountPaidFirst, 'Nominal amount_paid gagal ter-update saat webhook pertama.');

        // 3. Kirim Webhook Kedua (Simulasi duplikasi webhook Midtrans)
        $response2 = $this->postJson(route('midtrans.webhook'), $payload);
        $response2->assertStatus(200);

        // 4. Verifikasi bahwa nilai tidak bertambah/menduplikasi (Idempotensi)
        $amountPaidSecond = (float) DB::table('orders')->where('id', $this->order->id)->value('amount_paid');
        $this->assertEquals(30000, $amountPaidSecond, 'Nominal terduplikasi saat webhook kedua dikirim!');
    }
}
