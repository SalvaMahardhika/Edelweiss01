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
            'customer_name'  => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email'],
            'order_type'     => ['nullable', 'string', 'in:pickup,delivery'],
            'payment_plan'   => ['required', 'string', 'in:dp,full'],
            
            // 🔒 Perintah Utama: Validasi fulfill_at harus di masa depan (HTTP Level)
            'fulfill_at'     => ['required', 'date', 'after:now'],
            
            // Validasi kondisional untuk nominal DP jika menggunakan skema DP (10% - 90%)
            'total_amount'   => ['required_if:payment_plan,dp', 'numeric', 'min:0'],
            'dp_amount'      => [
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
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fulfill_at.after' => 'Tanggal pengambilan (fulfillment date) harus di masa depan.',
            'payment_plan.in'  => 'Skema pembayaran harus berupa dp atau full.',
        ];
    }
}