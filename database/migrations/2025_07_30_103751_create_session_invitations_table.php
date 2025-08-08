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
        Schema::create('session_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('padel_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->dateTime('responded_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['session_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index('invited_by');
            $table->index('responded_at');

            // Ensure unique invitation per session per user
            $table->unique(['session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_invitations');
    }
};
