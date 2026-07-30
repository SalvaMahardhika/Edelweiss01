<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $chatId;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN', '');
        $this->chatId = env('TELEGRAM_CHAT_ID', '');
    }

    /**
     * Kirim pesan ke Telegram Group dengan Topic ID tertentu.
     */
    public function sendMessage(string $message, ?string $topicId = null)
    {
        if (empty($this->token) || empty($this->chatId)) {
            Log::warning('Telegram Bot Token atau Chat ID belum dikonfigurasi di .env');
            return false;
        }

        $payload = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($topicId) {
            $payload['message_thread_id'] = $topicId;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi Telegram: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatter Notifikasi Pesanan Baru
     */
    public function sendOrderNotification($order)
    {
        // Get value order_type (aman jika berupa Backed Enum maupun String)
        $orderTypeValue = $order->order_type instanceof \BackedEnum 
            ? $order->order_type->value 
            : ($order->order_type->name ?? (string) $order->order_type);

        $isDelivery = strtolower($orderTypeValue) === 'delivery';
        $orderTypeLabel = $isDelivery ? '🚚 Kirim ke Alamat' : '🏪 Ambil di Toko (Pickup)';
        
        // --- 1. CEK SKEMA BAYAR (AMBIL DARI ENUM/STRING) ---
        $paymentPlanValue = $order->payment_plan instanceof \BackedEnum 
            ? $order->payment_plan->value 
            : ($order->payment_plan->name ?? (string) $order->payment_plan);

        $isDp = in_array(strtolower($paymentPlanValue), ['dp', 'down_payment', '1', 'true']);
        
        if ($isDp) {
            $paymentPlan = "Uang Muka (DP 50%)";
            
            $dpAmount = $order->dp_amount ?? ($order->total_amount / 2);
            $remainingAmount = $order->remaining_amount ?? ($order->total_amount - $dpAmount);

            $paymentDetails = "<b>Wajib Bayar Sekarang (DP):</b> Rp " . number_format($dpAmount, 0, ',', '.') . "\n";
            $paymentDetails .= "<b>Sisa Pelunasan:</b> Rp " . number_format($remainingAmount, 0, ',', '.') . "\n";
        } else {
            $paymentPlan = "Bayar Lunas (Full Payment)";
            $paymentDetails = "<b>Wajib Bayar Sekarang:</b> Rp " . number_format($order->total_amount, 0, ',', '.') . "\n";
        }

        // --- 2. FORMAT RINCIAN ITEM ---
        $itemsText = "";
        foreach ($order->items as $item) {
            $itemsText .= "• <b>{$item->product_name}</b> ({$item->quantity}x) - Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        // --- 3. SUSUN PESAN ---
        $message = "🥖 <b>PESANAN BARU MASUK!</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "<b>No. Order:</b> <code>{$order->order_number}</code>\n";
        $message .= "<b>Pelanggan:</b> {$order->customer_name} ({$order->customer_phone})\n";
        $message .= "<b>Metode:</b> {$orderTypeLabel}\n";

        // MENGGUNAKAN delivery_address SESUAI KOLOM DATABASE
        if ($isDelivery && !empty($order->delivery_address)) {
            $message .= "📍 <b>Alamat Kirim:</b> {$order->delivery_address}\n";
        }

        $message .= "<b>Tgl Penyiapan:</b> {$order->fulfill_at}\n";
        $message .= "<b>Skema Bayar:</b> {$paymentPlan}\n";
        $message .= "<b>Total Order:</b> Rp " . number_format($order->total_amount, 0, ',', '.') . "\n";
        $message .= $paymentDetails . "\n";
        
        $message .= "📦 <b>Rincian Item:</b>\n{$itemsText}\n";

        if (!empty($order->notes)) {
            $message .= "📝 <b>Catatan:</b> <i>{$order->notes}</i>\n";
        }

        $topicId = env('TELEGRAM_TOPIC_ORDERS');
        return $this->sendMessage($message, $topicId);
    }

    /**
     * Formatter Notifikasi Error Log Sistem
     */
    public function sendErrorLog(\Throwable $exception)
    {
        $message = "🚨 <b>SYSTEM ERROR DETECTED!</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "<b>Message:</b> <code>{$exception->getMessage()}</code>\n";
        $message .= "<b>File:</b> {$exception->getFile()}\n";
        $message .= "<b>Line:</b> {$exception->getLine()}\n";
        $message .= "<b>URL:</b> " . request()->fullUrl() . "\n";
        $message .= "<b>IP:</b> " . request()->ip() . "\n";

        $topicId = env('TELEGRAM_TOPIC_ERRORS');
        return $this->sendMessage($message, $topicId);
    }
}