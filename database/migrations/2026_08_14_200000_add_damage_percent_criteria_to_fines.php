<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fine_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('damage_light_percent')->default(25)->after('damaged_book_fee');
            $table->unsignedTinyInteger('damage_medium_percent')->default(50)->after('damage_light_percent');
            $table->unsignedTinyInteger('damage_heavy_percent')->default(75)->after('damage_medium_percent');
        });

        Schema::table('borrowing_details', function (Blueprint $table) {
            $table->string('condition', 20)->default('Baik')->after('return_date');
            $table->string('damage_level', 20)->nullable()->after('condition');
            $table->unsignedTinyInteger('damage_percent')->default(0)->after('damage_level');
            $table->unsignedInteger('book_price_snapshot')->default(0)->after('damage_percent');
            $table->unsignedInteger('late_fine')->default(0)->after('book_price_snapshot');
            $table->unsignedInteger('condition_fine')->default(0)->after('late_fine');
        });
    }

    public function down(): void
    {
        Schema::table('fine_settings', function (Blueprint $table) {
            $table->dropColumn([
                'damage_light_percent',
                'damage_medium_percent',
                'damage_heavy_percent',
            ]);
        });

        Schema::table('borrowing_details', function (Blueprint $table) {
            $table->dropColumn([
                'condition',
                'damage_level',
                'damage_percent',
                'book_price_snapshot',
                'late_fine',
                'condition_fine',
            ]);
        });
    }
};
