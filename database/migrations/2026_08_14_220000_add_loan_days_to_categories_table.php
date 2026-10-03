<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedSmallInteger('loan_days')->default(7)->after('status');
        });

        $categories = DB::table('categories')->get();
        foreach ($categories as $category) {
            $name = strtolower((string) $category->name);
            $days = 7;
            if (str_contains($name, 'paket') || str_contains($name, 'pelajaran') || str_contains($name, 'teks')) {
                $days = 360;
            } elseif (str_contains($name, 'referensi') || str_contains($name, 'ensiklo')) {
                $days = 0;
            }
            DB::table('categories')->where('id', $category->id)->update(['loan_days' => $days]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('loan_days');
        });
    }
};
