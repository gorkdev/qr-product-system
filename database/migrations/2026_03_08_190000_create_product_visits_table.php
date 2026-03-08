<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 64)->nullable();
            $table->string('browser', 128)->nullable();
            $table->string('platform', 64)->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();
            $table->index(['product_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_visits');
    }
};
