<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('stock_status', 20)->default('unverified')->after('jumlah_hilang');
            $table->timestamp('last_stock_take_at')->nullable()->after('stock_status');
            $table->foreignId('verified_by')->nullable()->after('last_stock_take_at')->constrained('users')->nullOnDelete();
            $table->index('stock_status');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['stock_status', 'last_stock_take_at']);
        });
    }
};
