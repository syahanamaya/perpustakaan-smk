<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('books', 'stock_status')) {
            return;
        }

        DB::table('books')
            ->where('stock_status', 'unverified')
            ->update(['stock_status' => 'verified']);

        if (Schema::hasColumn('books', 'jumlah_hilang')) {
            DB::table('books')->where('jumlah_hilang', '>', 0)->update(['stock_status' => 'missing']);
        }

        DB::table('books')
            ->where('stock_status', '!=', 'missing')
            ->where(function ($q) {
                $q->where('jumlah_rusak', '>', 0);
                if (Schema::hasColumn('books', 'jumlah_rusak_ringan')) {
                    $q->orWhere('jumlah_rusak_ringan', '>', 0)
                        ->orWhere('jumlah_rusak_sedang', '>', 0)
                        ->orWhere('jumlah_rusak_berat', '>', 0);
                }
            })
            ->update(['stock_status' => 'damaged']);

        DB::statement("ALTER TABLE books MODIFY stock_status VARCHAR(20) NOT NULL DEFAULT 'verified'");
    }

    public function down(): void
    {
        if (! Schema::hasColumn('books', 'stock_status')) {
            return;
        }

        DB::statement("ALTER TABLE books MODIFY stock_status VARCHAR(20) NOT NULL DEFAULT 'unverified'");
    }
};
