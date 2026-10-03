<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Cek dulu: Apakah kolom 'major' SUDAH ADA?
        if (!Schema::hasColumn('students', 'major')) {
            // Kalau BELUM ada, baru buat.
            Schema::table('students', function (Blueprint $table) {
                $table->string('major')->nullable()->after('class');
            });
        }
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('major');
        });
    }
};
