<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        // Validasi input HTTP otomatis berjalan di StoreOrderRequest sebelum masuk ke sini.
        // Jika gagal, Laravel otomatis mengembalikan error 422 kembali ke user.

        $order = Order::create([
            'order_number' => 'EDL-'.Carbon::now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'order_type' => $request->input('order_type', 'pickup'),
            'status' => 'pending',
            'payment_plan' => $request->payment_plan,
            'payment_status' => 'unpaid',
            'fulfill_at' => $request->fulfill_at,
            'placed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Order dengan nomor '.$order->order_number.' berhasil dibuat!');
    }
}
