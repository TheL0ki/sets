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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('padel_sessions')->onDelete('cascade');
            $table->integer('match_number')->default(0);
            $table->integer('team_a_score')->default(0);
            $table->integer('team_b_score')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index(['session_id', 'match_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
