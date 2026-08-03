<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disabled_dates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // Tanggal yang dikunci (Format: YYYY-MM-DD)
            $table->string('reason')->nullable(); // Alasan (Misal: "Kuota Penuh", "Libur Hari Raya", dll)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disabled_dates');
    }
};
