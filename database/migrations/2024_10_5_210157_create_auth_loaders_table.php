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
        Schema::create('auth_loaders', function (Blueprint $table) {
            $table->id();
            $table->string('lang');
            $table->string('loader_type')->default('no_ui');
            $table->decimal('version',8,2);
            $table->string('hash');
            $table->string('path');
            $table->timestamp('unsupported_at')->nullable();
            $table->json('tags')->nullable();
            $table->json('update_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_loaders');
    }
};
