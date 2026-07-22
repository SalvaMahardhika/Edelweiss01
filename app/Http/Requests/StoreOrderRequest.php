<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Izinkan semua user yang terautentikasi
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'order_type' => ['required', 'in:pickup,dine_in,delivery'],

            // 🔑 PERBAIKAN: Menggunakan ekspresi "+2 hours" secara dinamis agar Carbon/Laravel mengevaluasinya realtime saat request diproses
            'fulfill_at' => ['required', 'date', 'after:+2 hours'],

            'payment_plan' => ['required', 'in:dp,full'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Validasi kondisional untuk nominal DP jika menggunakan skema DP (10% - 90%)
            'total_amount' => ['required_if:payment_plan,dp', 'numeric', 'min:0'],
            'dp_amount' => [
                'required_if:payment_plan,dp',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->input('payment_plan') === 'dp') {
                        $total = $this->input('total_amount', 0);
                        $min = $total * 0.10;
                        $max = $total * 0.90;

                        if ($value < $min || $value > $max) {
                            $fail('Nominal down-payment (DP) harus berada di antara 10% hingga 90% dari total transaksi.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fulfill_at.after' => 'Tanggal pengambilan (fulfillment date) harus di masa depan dengan jeda minimal 2 jam dari sekarang.',
            'payment_plan.in' => 'Skema pembayaran harus berupa dp atau full.',
        ];
    }
}
