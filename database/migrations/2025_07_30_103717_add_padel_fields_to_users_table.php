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
            $table->string('skill_level')->nullable()->after('email');
            $table->integer('preferred_frequency_per_week')->default(1)->after('skill_level');
            $table->string('phone')->nullable()->after('preferred_frequency_per_week');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'skill_level',
                'preferred_frequency_per_week',
                'phone',
                'is_active',
            ]);
        });
    }
};
