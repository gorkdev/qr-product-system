<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_visits', function (Blueprint $table) {
            $table->string('region_name', 128)->nullable()->after('country');
            $table->string('timezone', 64)->nullable()->after('region_name');
            $table->string('isp', 255)->nullable()->after('timezone');
            $table->decimal('lat', 10, 8)->nullable()->after('isp');
            $table->decimal('lon', 11, 8)->nullable()->after('lat');
            $table->text('error_message')->nullable()->after('visited_at');
            $table->boolean('is_anonymous')->default(false)->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('product_visits', function (Blueprint $table) {
            $table->dropColumn(['region_name', 'timezone', 'isp', 'lat', 'lon', 'error_message', 'is_anonymous']);
        });
    }
};
