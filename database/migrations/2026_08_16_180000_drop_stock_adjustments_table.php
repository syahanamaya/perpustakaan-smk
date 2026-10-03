<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }

    public function down(): void
    {
        // Tabel riwayat opname sudah tidak dipakai; tidak dibuat ulang.
    }
};
