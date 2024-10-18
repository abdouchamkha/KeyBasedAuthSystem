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
        Schema::create('license_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id')->nullable();
            $table->unsignedBigInteger('app_id');
            $table->uuid('uuid_value')->nullable();
            $table->uuid('token')->un();
            $table->string('ip');
            $table->integer('duration'); // in seconds
            $table->string('type'); // init or license or download

            // Add foreign keys
            $table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_sessions');
    }
};
