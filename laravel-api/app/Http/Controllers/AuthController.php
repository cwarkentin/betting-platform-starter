<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'email' => ['unique:users,email', 'required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        return DB::transaction(function () use ($request) { 
            $data = $request->only(['name', 'email', 'password']);  
            /** @var User $user */                                                        
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            Wallet::create([
                'user_id' => $user->id,
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['token' => $token], 201);
        });
    }

    public function login(Request $request) 
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8']
        ]);

        $data = $request->all();
        $user = User::where('email', $data['email'])->first();

        if(is_null($user) || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials']);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['token' => $token], 200);
    }

    public function logout(Request $request) 
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}