<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('books', function (Blueprint $table) {
        $table->id();
        $table->string('book_code')->unique();
        $table->string('title');
        
        // Menghubungkan ke tabel categories
        $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        $table->foreignId('rak_id')->constrained('raks')->onDelete('cascade');

        $table->string('author');
        $table->string('publisher');
        $table->string('isbn')->nullable();
        $table->year('publication_year');
        $table->integer('stock')->default(0);
        $table->string('cover_image')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
