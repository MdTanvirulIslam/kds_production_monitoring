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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number', 20)->unique()->comment('T001, T002, etc.');
            $table->string('table_name', 100)->comment('Table 1, Table 2');
            $table->string('qr_code')->unique()->comment('Generated QR code string')->nullable();
            $table->string('esp32_ip', 45)->nullable()->comment('ESP32 device IP address');
            $table->string('esp32_device_id', 50)->nullable()->comment('ESP32_T001, etc.');
            $table->enum('current_light_status', ['off', 'red', 'green', 'blue'])->default('off');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for faster queries
            $table->index('table_number');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
