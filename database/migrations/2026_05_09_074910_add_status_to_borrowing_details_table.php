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
        Schema::table('borrowing_details', function (Blueprint $table) {
            // Menambah kolom status dan tanggal kembali khusus untuk per-buku
            $table->enum('status', ['borrowed', 'returned'])->default('borrowed')->after('qty');
            $table->dateTime('return_date')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('borrowing_details', function (Blueprint $table) {
            $table->dropColumn(['status', 'return_date']);
        });
    }
};
