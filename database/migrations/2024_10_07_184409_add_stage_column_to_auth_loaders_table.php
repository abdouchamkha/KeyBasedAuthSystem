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
        Schema::table('auth_loaders', function (Blueprint $table) {
            $table->enum('stage',['production','staging','development'])->default('development')->after('lang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_loaders', function (Blueprint $table) {
            $table->dropColumn('stage');
        });
    }
};
