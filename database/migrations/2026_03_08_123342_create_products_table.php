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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Agent 1 & 4: Tahmin edilemez URL için benzersiz UUID
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->text('description')->nullable();

            // Medya alanları (JSON olarak saklamak esneklik sağlar)
            $table->json('images')->nullable(); // Çoklu görsel
            $table->json('videos')->nullable(); // Çoklu YouTube URL
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            // Agent 3: Performans için index
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
