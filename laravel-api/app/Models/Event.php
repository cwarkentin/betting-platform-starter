<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
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
