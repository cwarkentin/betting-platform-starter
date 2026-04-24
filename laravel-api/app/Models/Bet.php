<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $fillable = [
        'user_id', 'event_id', 'selection',
        'odds_snapshot', 'amount', 'potential_payout', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'odds_snapshot'    => 'decimal:2',
            'potential_payout' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
