<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BetPlacementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Wallet $wallet;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 500.00,
            'status'  => 'active',
        ]);
        $this->event = Event::factory()->create([
            'status'    => 'upcoming',
            'home_odds' => 2.50,
            'away_odds' => 3.00,
            'draw_odds' => 3.20,
        ]);
    }

    public function test_user_can_place_bet_on_upcoming_event(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/bets', [
            'event_id'  => $this->event->id,
            'selection' => 'home',
            'amount'    => 100.00,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'selection', 'amount', 'potential_payout', 'status']]);

        $this->assertDatabaseHas('bets', [
            'user_id'   => $this->user->id,
            'event_id'  => $this->event->id,
            'selection' => 'home',
            'amount'    => 100.00,
            'status'    => 'pending',
        ]);

        // Balance should be deducted
        $this->assertEquals(400.00, $this->wallet->fresh()->balance);

        // Transaction recorded
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id'      => $this->wallet->id,
            'type'           => 'bet_placed',
            'amount'         => 100.00,
            'balance_before' => 500.00,
            'balance_after'  => 400.00,
        ]);
    }

    public function test_bet_is_rejected_when_balance_is_insufficient(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/bets', [
            'event_id'  => $this->event->id,
            'selection' => 'home',
            'amount'    => 1000.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);

        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals(500.00, $this->wallet->fresh()->balance);
    }

    public function test_bet_is_rejected_on_non_upcoming_event(): void
    {
        $this->event->update(['status' => 'completed']);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/bets', [
            'event_id'  => $this->event->id,
            'selection' => 'home',
            'amount'    => 50.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['event_id']);

        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals(500.00, $this->wallet->fresh()->balance);
    }

    public function test_bet_is_rejected_when_wallet_is_not_active(): void 
    {
        $this->wallet->update(['status' => 'frozen']);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/bets', [
            'event_id'  => $this->event->id,
            'selection' => 'home',
            'amount'    => 100.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);

        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals(500.00, $this->wallet->fresh()->balance);
    }

    public function test_unauthenticated_user_cannot_place_bet(): void
    {
        $response = $this->postJson('/api/bets', [
            'event_id'  => $this->event->id,
            'selection' => 'home',
            'amount'    => 50.00,
        ]);

        $response->assertStatus(401);
    }
}
