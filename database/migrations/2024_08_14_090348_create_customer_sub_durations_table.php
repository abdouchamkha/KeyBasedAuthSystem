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
        Schema::create('customer_sub_durations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscripton_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedBigInteger('days_left')->nullable();

            // Add foreign keys
            // $table->foreign('supscripton_id')->references('id')->on('customer_subs')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sub_durations');
    }
};
