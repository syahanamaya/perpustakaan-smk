<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('stok_sistem')->default(0);
            $table->unsignedInteger('stok_dipinjam')->default(0);
            $table->unsignedInteger('stok_master')->default(0);
            $table->unsignedInteger('stok_fisik');
            $table->integer('selisih')->default(0);
            $table->string('foto_path')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['menunggu_validasi', 'ditolak', 'disetujui'])->default('menunggu_validasi');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('resubmitted_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'submitted_at']);
            $table->index(['book_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
