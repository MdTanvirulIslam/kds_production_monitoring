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
        Schema::create('button_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->string('device_id', 50);
            $table->string('table_number', 20);
            $table->string('alert_type', 50)->default('button_press');
            $table->string('previous_color', 20)->nullable();
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('set null');
            $table->boolean('is_read')->default(false);
            $table->foreignId('read_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('pressed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['table_id', 'pressed_at']);
            $table->index(['is_read', 'pressed_at']);
            $table->index('device_id');
        });

        // Add ESP32 columns to tables table if not exists
        if (!Schema::hasColumn('tables', 'esp32_device_id')) {
            Schema::table('tables', function (Blueprint $table) {
                $table->string('esp32_device_id', 50)->nullable()->after('current_light_status');
                $table->string('esp32_ip', 45)->nullable()->after('esp32_device_id');
                $table->integer('esp32_rssi')->nullable()->after('esp32_ip');
                $table->boolean('esp32_online')->default(false)->after('esp32_rssi');
                $table->timestamp('esp32_last_seen')->nullable()->after('esp32_online');
            });
        }

        // Add triggered_by column to light_indicators if not exists
        if (!Schema::hasColumn('light_indicators', 'triggered_by')) {
            Schema::table('light_indicators', function (Blueprint $table) {
                $table->enum('triggered_by', ['supervisor', 'system', 'button'])->default('supervisor')->after('reason');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('button_notifications');

        if (Schema::hasColumn('tables', 'esp32_device_id')) {
            Schema::table('tables', function (Blueprint $table) {
                $table->dropColumn([
                    'esp32_device_id',
                    'esp32_ip',
                    'esp32_rssi',
                    'esp32_online',
                    'esp32_last_seen',
                ]);
            });
        }

        if (Schema::hasColumn('light_indicators', 'triggered_by')) {
            Schema::table('light_indicators', function (Blueprint $table) {
                $table->dropColumn('triggered_by');
            });
        }
    }
};
