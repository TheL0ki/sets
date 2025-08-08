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
        Schema::create('player_ignores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ignorer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ignored_id')->constrained('users')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['ignorer_id', 'ignored_id']);
            $table->index('ignored_id');

            // Ensure unique ignore relationship
            $table->unique(['ignorer_id', 'ignored_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_ignores');
    }
};
