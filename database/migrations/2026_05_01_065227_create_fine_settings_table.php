<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fine_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('late_fee_per_day')->default(1000);
            $table->integer('max_fee_per_book')->default(50000);
            $table->integer('tolerance_days')->default(1);
            $table->integer('damaged_book_fee')->default(10000);
            $table->string('lost_book_fee_type')->default('Sesuai Harga Buku');
            $table->string('rounding_rule')->default('Dibulatkan ke atas (Ribuan)');
            $table->integer('max_fee_per_transaction')->default(200000);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users'); // Mencatat siapa Kepala Perpus yang mengubah
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_settings');
    }
};