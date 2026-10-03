<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_loan_rules', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_paket')->default(4)->after('max_books');
            $table->unsignedTinyInteger('max_bebas')->default(2)->after('max_paket');
        });

        $rules = DB::table('class_loan_rules')->get();
        foreach ($rules as $rule) {
            DB::table('class_loan_rules')->where('id', $rule->id)->update([
                'max_paket' => max(1, (int) ($rule->max_books ?: 4)),
                'max_bebas' => 2,
            ]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('quota_group', 20)->default('bebas')->after('loan_days');
        });

        $categories = DB::table('categories')->get();
        foreach ($categories as $category) {
            $name = strtolower((string) $category->name);
            $group = 'bebas';
            if (
                str_contains($name, 'paket')
                || str_contains($name, 'pelajaran')
                || str_contains($name, 'teks')
                || str_contains($name, 'wajib')
            ) {
                $group = 'paket';
            }
            DB::table('categories')->where('id', $category->id)->update(['quota_group' => $group]);
        }
    }

    public function down(): void
    {
        Schema::table('class_loan_rules', function (Blueprint $table) {
            $table->dropColumn(['max_paket', 'max_bebas']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('quota_group');
        });
    }
};
