<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('share_token', 64)->unique()->nullable()->after('uuid');
        });

        DB::table('products')->whereNull('share_token')->get()->each(function ($row) {
            DB::table('products')->where('id', $row->id)->update(['share_token' => Str::random(64)]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
