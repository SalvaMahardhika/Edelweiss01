<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // EDL-20260703-0001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');

            $table->string('order_type')->default('pickup'); // pickup | delivery
            $table->text('delivery_address')->nullable();
            $table->string('status')->default('pending'); // pending | confirmed | preparing | ready | completed | cancelled

            $table->string('payment_plan')->default('full'); // dp | full
            $table->string('payment_status')->default('unpaid'); // unpaid | partial | paid | failed | refunded
            $table->string('snap_token')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('dp_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->dateTime('fulfill_at')->nullable();
            $table->dateTime('settlement_due_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'payment_status']);
            $table->index('fulfill_at');
            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
