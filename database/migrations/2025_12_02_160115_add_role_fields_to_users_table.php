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
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'supervisor', 'monitor'])->default('monitor')->after('password');
                $table->boolean('is_active')->default(true)->after('role');
                $table->timestamp('last_login_at')->nullable()->after('phone');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['role', 'is_active', 'phone', 'last_login_at']);
            });
        });
    }
};
