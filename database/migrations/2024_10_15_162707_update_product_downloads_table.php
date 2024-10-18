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
        Schema::table('product_downloads', function (Blueprint $table) {
            $table->json('labels')->nullable(); // Store labels (e.g., size, date)
            $table->json('tags')->nullable()->change(); // Allow multiple tags as JSON
            $table->dropColumn('type'); // Remove the 'type' column
        });
    }

    public function down(): void
    {
        Schema::table('product_downloads', function (Blueprint $table) {
            $table->dropColumn('labels');
            $table->string('tags')->change();
            $table->string('type'); // Revert the type column if needed
        });
    }
};
