<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'sport_type', 'home_team', 'away_team',
        'home_odds', 'away_odds', 'draw_odds',
        'status', 'result', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
