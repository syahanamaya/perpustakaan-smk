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
        Schema::table('borrowings', function (Blueprint $table) {
            // Tambahkan kolom fine_status jika belum ada
            if (!Schema::hasColumn('borrowings', 'fine_status')) {
                $table->string('fine_status')->default('unpaid')->after('total_fine');
            }
            
            // Tambahkan kolom denda_dibayar (Gunakan decimal agar presisi)
            $table->decimal('denda_dibayar', 15, 2)->default(0)->after('fine_status');
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn(['fine_status', 'denda_dibayar']);
        });
    }
};
