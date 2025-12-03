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
        Schema::table('light_indicators', function (Blueprint $table) {
            if (!Schema::hasColumn('light_indicators', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('light_indicators', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable();
            }
            if (!Schema::hasColumn('light_indicators', 'activated_at')) {
                $table->timestamp('activated_at')->nullable();
            }
            if (!Schema::hasColumn('light_indicators', 'reason')) {
                $table->string('reason', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('light_indicators', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'deactivated_at', 'activated_at', 'reason']);
        });
    }
};
