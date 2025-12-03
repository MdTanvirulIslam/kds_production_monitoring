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
        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('workers')->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->date('production_date');
            $table->time('production_hour')->comment('Hour of production (09:00:00, 10:00:00, etc.)');
            $table->integer('garments_count')->unsigned()->comment('Number of garments produced');
            $table->string('product_type', 100)->nullable()->comment('Shirt, Pant, etc.');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['production_date', 'production_hour']);
            $table->index('worker_id');
            $table->index('table_id');
            $table->index('production_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_logs');
    }
};
