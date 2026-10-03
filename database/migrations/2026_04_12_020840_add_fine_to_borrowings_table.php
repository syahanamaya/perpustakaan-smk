<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            // Menambahkan kolom denda setelah kolom status
            $table->integer('fine_amount')->default(0)->after('status');
            $table->enum('fine_status', ['no_fine', 'unpaid', 'paid'])->default('no_fine')->after('fine_amount');
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn(['fine_amount', 'fine_status']);
        });
    }
};