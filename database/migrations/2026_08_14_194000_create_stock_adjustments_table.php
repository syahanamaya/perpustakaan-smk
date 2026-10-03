<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('stok_awal')->default(0);
            $table->integer('stok_fisik')->default(0);
            $table->integer('selisih')->default(0);
            $table->string('kondisi', 20)->default('baik'); // baik | rusak | hilang
            $table->unsignedInteger('jumlah_rusak')->default(0);
            $table->unsignedInteger('jumlah_hilang')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::table('books', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_rusak')->default(0)->after('stock');
            $table->unsignedInteger('jumlah_hilang')->default(0)->after('jumlah_rusak');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['jumlah_rusak', 'jumlah_hilang']);
        });

        Schema::dropIfExists('stock_adjustments');
    }
};
