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
        Schema::create('light_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('set null');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->enum('light_color', ['red', 'green', 'blue', 'off']);
            $table->string('reason', 200)->nullable()->comment('Quality issue, Good work, Need attention, etc.');
            $table->text('notes')->nullable();
            $table->timestamp('activated_at');
            $table->timestamp('deactivated_at')->nullable();
            $table->integer('duration_seconds')->nullable()->comment('How long light was on');
            $table->timestamps();

            // Indexes
            $table->index(['table_id', 'activated_at']);
            $table->index('light_color');
            $table->index(['activated_at', 'deactivated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('light_indicators');
    }
};
