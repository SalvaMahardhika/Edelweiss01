<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('produk')->nullOnDelete();

            $table->string('product_name'); // snapshot nama saat dibeli
            $table->decimal('unit_price', 12, 2); // snapshot harga saat dibeli
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->string('notes')->nullable();

            $table->timestamps();
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};