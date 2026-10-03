<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'jumlah_rusak_ringan')) {
                $table->unsignedInteger('jumlah_rusak_ringan')->default(0)->after('jumlah_rusak');
            }
            if (! Schema::hasColumn('books', 'jumlah_rusak_sedang')) {
                $table->unsignedInteger('jumlah_rusak_sedang')->default(0)->after('jumlah_rusak_ringan');
            }
            if (! Schema::hasColumn('books', 'jumlah_rusak_berat')) {
                $table->unsignedInteger('jumlah_rusak_berat')->default(0)->after('jumlah_rusak_sedang');
            }
        });

        if (Schema::hasColumn('books', 'jumlah_rusak') && Schema::hasColumn('books', 'jumlah_rusak_sedang')) {
            DB::table('books')
                ->where('jumlah_rusak', '>', 0)
                ->where('jumlah_rusak_ringan', 0)
                ->where('jumlah_rusak_sedang', 0)
                ->where('jumlah_rusak_berat', 0)
                ->update([
                    'jumlah_rusak_sedang' => DB::raw('jumlah_rusak'),
                ]);
        }

        if (Schema::hasTable('stock_adjustments') && ! Schema::hasColumn('stock_adjustments', 'tingkat_rusak')) {
            Schema::table('stock_adjustments', function (Blueprint $table) {
                $table->string('tingkat_rusak', 20)->nullable()->after('kondisi');
            });
        }
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $cols = array_values(array_filter([
                Schema::hasColumn('books', 'jumlah_rusak_ringan') ? 'jumlah_rusak_ringan' : null,
                Schema::hasColumn('books', 'jumlah_rusak_sedang') ? 'jumlah_rusak_sedang' : null,
                Schema::hasColumn('books', 'jumlah_rusak_berat') ? 'jumlah_rusak_berat' : null,
            ]));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        if (Schema::hasTable('stock_adjustments') && Schema::hasColumn('stock_adjustments', 'tingkat_rusak')) {
            Schema::table('stock_adjustments', function (Blueprint $table) {
                $table->dropColumn('tingkat_rusak');
            });
        }
    }
};
