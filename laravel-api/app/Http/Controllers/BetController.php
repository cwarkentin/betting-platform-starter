<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BettingService;

class BetController extends Controller
{
    public function __construct(private BettingService $bettingService) {}

    public function store(Request $request)
    {              
        $bet = $this->bettingService->placeBet($request->user(), $request->all());                                                                                                                              
        return response()->json(['data' => $bet], 201);
    }
}
