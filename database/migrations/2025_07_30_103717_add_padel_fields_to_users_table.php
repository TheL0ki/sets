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
            $table->integer('preferred_frequency_per_week')->default(1)->after('email');
            $table->unsignedTinyInteger('preferred_frequency_per_month')->default(4)->after('preferred_frequency_per_week'); 
            $table->unsignedTinyInteger('min_session_length_hours')->default(2)->after('preferred_frequency_per_month');
            $table->unsignedTinyInteger('max_session_length_hours')->default(4)->after('min_session_length_hours');
            $table->string('phone')->nullable()->after('max_session_length_hours');
            $table->boolean('phone_visible')->default(false)->after('phone');
            $table->boolean('email_visible')->default(false)->after('phone_visible');
            $table->boolean('is_active')->default(true)->after('email_visible');

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
