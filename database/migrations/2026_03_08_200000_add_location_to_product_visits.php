<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_visits', function (Blueprint $table) {
            $table->string('city', 128)->nullable()->after('platform');
            $table->string('country', 128)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('product_visits', function (Blueprint $table) {
            $table->dropColumn(['city', 'country']);
        });
    }
};
