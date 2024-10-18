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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('hwid_lock');
            $table->string('license_value')->nullable();
            $table->uuid('uuid_value')->unique()->index();
            $table->timestamp('frozen_at')->nullable();
            $table->enum('freeze_type',['admin','timer','default'])->nullable(); // in case of timer there will be a rules for customer an if admin he can freeze as much as he want
            $table->timestamp('unfreeze_at')->nullable(); // This will set if the freeze_type is timer
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->bigInteger('extra_time')->nullable(); // store as hours with big integer
            $table->bigInteger('subscription_duration')->nullable(); // store as hours with big integer

            // Add foreign keys
            $table->foreign('app_id')->references('id')->on('applications')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
