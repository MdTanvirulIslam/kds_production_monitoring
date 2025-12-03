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
        Schema::create('table_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('workers')->onDelete('cascade');
            $table->date('assigned_date');
            $table->time('shift_start')->nullable()->comment('e.g., 08:00:00');
            $table->time('shift_end')->nullable()->comment('e.g., 17:00:00');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['table_id', 'assigned_date']);
            $table->index(['worker_id', 'assigned_date']);
            $table->index('status');

            // Ensure one worker per table per date
            $table->unique(['table_id', 'assigned_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_assignments');
    }
};
