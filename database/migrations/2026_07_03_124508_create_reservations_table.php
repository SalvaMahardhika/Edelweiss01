<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->dateTime('reserved_at');
            $table->unsignedSmallInteger('party_size');
            $table->string('status')->default('pending'); // pending|confirmed|seated|completed|cancelled|no_show
            $table->string('table_number')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->index(['reserved_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
