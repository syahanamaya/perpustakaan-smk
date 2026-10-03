<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_loan_rules', function (Blueprint $table) {
            $table->id();
            $table->string('class_level', 10)->unique(); // X, XI, XII
            $table->unsignedTinyInteger('max_books')->default(3);
            $table->unsignedSmallInteger('loan_days')->default(7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_loan_rules');
    }
};
