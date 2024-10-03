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
        Schema::create('license_hwids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id')->nullable();
            $table->unsignedBigInteger('app_id');
            $table->unsignedBigInteger('product_id');
            $table->uuid('uuid_value');
            $table->string('ip');
            $table->string('hwid');
            $table->timestamp('banned_at')->nullable(); // Nullable in case it's not banned yet
            $table->enum('ban_type', ['app', 'product', 'license'])->nullable(); // Enum for select-like functionality
            $table->timestamp('last_active')->nullable(); // represents the last time that the license has been loaded

            // Add foreign keys
            $table->foreign('license_id')->references('id')->on('licenses')->nullOnDelete();
            $table->foreign('app_id')->references('id')->on('applications')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_hwids');
    }
};
