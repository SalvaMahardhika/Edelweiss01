<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Pembuat/Pengelola

            $table->string('nama_produk', 100);
            $table->string('slug')->nullable()->unique();
            $table->string('gambar', 255)->nullable();
            $table->decimal('harga', 12, 2);
            $table->text('deskripsi')->nullable();
            $table->boolean('status')->default(true);       // Tampil di katalog
            $table->boolean('is_available')->default(true);  // Stok tersedia
            $table->boolean('is_featured')->default(false);  // Unggulan

            $table->softDeletes();
            $table->timestamps();

            $table->index(['category_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};