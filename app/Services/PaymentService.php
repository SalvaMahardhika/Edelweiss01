<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentType;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Mencatat transaksi pembayaran baru atau memproses callback pembayaran.
     * Menggunakan transaksi database dan pengecekan reference unik untuk mencegah double callback.
     *
     * @param Order $order
     * @param array $paymentData Array berisi [amount, type, reference, status, method, provider, dsb.]
     * @return Payment
     */
    public function processPayment(Order $order, array $paymentData): Payment
    {
        return DB::transaction(function () use ($order, $paymentData) {
            $reference = $paymentData['reference'] ?? null;

            // 1. Cegah double callback: Jika reference sudah sukses tercatat, abaikan proses ulang
            if ($reference) {
                $existingPayment = Payment::where('reference', $reference)
                    ->whereIn('status', ['settlement', 'paid'])
                    ->first();

                if ($existingPayment) {
                    // Double callback terdeteksi! Langsung kembalikan data yang ada tanpa update/tambah nominal
                    return $existingPayment;
                }
            }

            // 2. Jika transaksi dengan reference ini sudah terdaftar sebagai 'pending', lakukan update
            $payment = null;
            if ($reference) {
                $payment = Payment::where('reference', $reference)->first();
            }

            $isSuccessStatus = isset($paymentData['status']) && in_array($paymentData['status'], ['settlement', 'paid']);

            if ($payment) {
                $payment->update([
                    'status' => $paymentData['status'] ?? 'settlement',
                    'method' => $paymentData['method'] ?? $payment->method,
                    'payload' => isset($paymentData['payload']) ? json_encode($paymentData['payload']) : $payment->payload,
                    'paid_at' => $isSuccessStatus ? now() : $payment->paid_at,
                ]);
            } else {
                // 3. Buat catatan pembayaran baru
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'type' => $paymentData['type'] ?? PaymentType::Full,
                    'provider' => $paymentData['provider'] ?? 'midtrans',
                    'method' => $paymentData['method'] ?? 'qris',
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'] ?? 'IDR',
                    'status' => $paymentData['status'] ?? 'pending',
                    'reference' => $reference,
                    'snap_token' => $paymentData['snap_token'] ?? null,
                    'payload' => isset($paymentData['payload']) ? json_encode($paymentData['payload']) : null,
                    'paid_at' => $isSuccessStatus ? now() : null,
                ]);
            }

            // Refresh order instance agar nominal ter-update secara real-time pada memori PHP
            $order->refresh();

            return $payment;
        });
    }
}
