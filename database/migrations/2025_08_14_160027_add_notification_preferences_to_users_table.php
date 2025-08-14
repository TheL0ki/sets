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
            $table->boolean('email_notifications_enabled')->default(true)->after('is_active');
            $table->boolean('session_invitation_notifications')->default(true)->after('email_notifications_enabled');
            $table->boolean('session_confirmation_notifications')->default(true)->after('session_invitation_notifications');
            $table->boolean('session_reminder_notifications')->default(true)->after('session_confirmation_notifications');
            $table->boolean('session_cancellation_notifications')->default(true)->after('session_reminder_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications_enabled',
                'session_invitation_notifications',
                'session_confirmation_notifications',
                'session_reminder_notifications',
                'session_cancellation_notifications',
            ]);
        });
    }
};
