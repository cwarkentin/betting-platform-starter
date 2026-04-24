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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('selection'); // 'home', 'away', 'draw'
            $table->decimal('odds_snapshot', 8, 2);
            $table->decimal('amount', 15, 2);
            $table->decimal('potential_payout', 15, 2);
            $table->enum('status', ['pending', 'won', 'lost', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
