<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\User;
use App\Models\Event;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BettingService
{
    public function placeBet(User $user, array $data): Bet
    {
        $wallet = $user->wallet;

        if ($wallet->status !== 'active') {
            throw ValidationException::withMessages([
                'status' => 'Wallet is not active.',
            ]);
        }

        if ($wallet->balance < $data['amount']) {
            throw ValidationException::withMessages([
                'amount' => 'Invalid balance.',
            ]);
        }

        $event = Event::findOrFail($data['event_id']);

        if ($event->status !== 'upcoming') {
            throw ValidationException::withMessages(['event_id' => 'Event is not available for betting.']);
        }

        return DB::transaction(function () use ($user, $wallet, $event, $data) {                                                                     
            $bet = Bet::create([
                'user_id' => $user->id,
                'event_id' => $data['event_id'],
                'selection' => $data['selection'],
                'amount' => $data['amount'],
                'potential_payout' => $data['amount'] * $event->{$data['selection'] . '_odds'},
                'odds_snapshot' => $event->{$data['selection'] . '_odds'},
                'status' => 'pending',
            ]);
            $balanceBefore = $wallet->balance;
            $wallet->update([
                'balance' => $wallet->balance - $data['amount'],
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'bet_placed',
                'amount' => $data['amount'],
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->fresh()->balance,
                'bet_id' => $bet->id,
                'description' => 'Bet placed.',
            ]);

            return $bet;
        });
    }
}
