<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('students', 'member_number') ? 'member_number' : null,
                Schema::hasColumn('students', 'entry_year') ? 'entry_year' : null,
            ]));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'member_number')) {
                $table->string('member_number')->nullable();
            }
            if (! Schema::hasColumn('students', 'entry_year')) {
                $table->string('entry_year')->nullable();
            }
        });
    }
};
