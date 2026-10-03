<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_hilang_sistem')->default(0)->after('stok_master');
            $table->unsignedInteger('jumlah_rusak_ringan_sistem')->default(0)->after('jumlah_hilang_sistem');
            $table->unsignedInteger('jumlah_rusak_sedang_sistem')->default(0)->after('jumlah_rusak_ringan_sistem');
            $table->unsignedInteger('jumlah_rusak_berat_sistem')->default(0)->after('jumlah_rusak_sedang_sistem');

            $table->unsignedInteger('jumlah_hilang_ditemukan')->default(0)->after('stok_fisik');
            $table->unsignedInteger('jumlah_rusak_ringan')->default(0)->after('jumlah_hilang_ditemukan');
            $table->unsignedInteger('jumlah_rusak_sedang')->default(0)->after('jumlah_rusak_ringan');
            $table->unsignedInteger('jumlah_rusak_berat')->default(0)->after('jumlah_rusak_sedang');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_hilang_sistem',
                'jumlah_rusak_ringan_sistem',
                'jumlah_rusak_sedang_sistem',
                'jumlah_rusak_berat_sistem',
                'jumlah_hilang_ditemukan',
                'jumlah_rusak_ringan',
                'jumlah_rusak_sedang',
                'jumlah_rusak_berat',
            ]);
        });
    }
};
