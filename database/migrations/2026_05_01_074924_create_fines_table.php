<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            // Menyambungkan denda dengan data peminjaman
            $table->foreignId('borrowing_id')->constrained('borrowings')->onDelete('cascade'); 
            
            $table->integer('total_denda'); // Total denda yang harus dibayar
            $table->integer('denda_dibayar')->default(0); // Uang yang sudah masuk
            $table->string('status_pembayaran')->default('Belum Lunas'); // Belum Lunas, Lunas
            
            $table->text('keterangan')->nullable(); // Misal: "Terlambat 5 hari + Buku Rusak"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};