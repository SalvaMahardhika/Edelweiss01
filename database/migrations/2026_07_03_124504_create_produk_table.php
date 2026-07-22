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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // pembuat/pengelola produk

            $table->string('nama_produk', 45);
            $table->string('slug')->nullable()->unique();
            $table->string('gambar', 100)->nullable();
            $table->decimal('harga', 10, 2);
            $table->text('deskripsi')->nullable();
            $table->boolean('status')->default(true);       // tampil/tidak di katalog lama
            $table->boolean('is_available')->default(true);  // stok tersedia untuk dipesan
            $table->boolean('is_featured')->default(false);  // menu unggulan

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
