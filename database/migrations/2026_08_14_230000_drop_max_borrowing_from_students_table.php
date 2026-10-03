<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('students', 'max_borrowing')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('max_borrowing');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('students', 'max_borrowing')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedTinyInteger('max_borrowing')->nullable()->after('entry_year');
            });
        }
    }
};
