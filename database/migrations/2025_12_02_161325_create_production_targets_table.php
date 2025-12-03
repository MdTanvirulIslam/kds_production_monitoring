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
        Schema::create('production_targets', function (Blueprint $table) {
            $table->id();
            $table->date('target_date')->unique();
            $table->integer('hourly_target')->unsigned()->comment('Expected garments per hour');
            $table->integer('daily_target')->unsigned()->comment('Expected garments per day');
            $table->string('product_type', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('target_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_targets');
    }
};
