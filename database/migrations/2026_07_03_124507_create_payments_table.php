<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->string('type')->default('full'); // down_payment | settlement | full
            $table->string('provider')->default('midtrans');
            $table->string('method')->nullable(); // qris | gopay | bank_transfer, dll.
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('status')->default('pending'); // pending | settlement | paid | failed | expired | refunded
            $table->string('reference')->nullable()->unique(); // ID Transaksi Midtrans
            $table->string('snap_token')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
