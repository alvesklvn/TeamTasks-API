<?php

namespace App\Domain\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use JsonException;

class AuthService
{
    private const DUMMY_HASH = "\$2y\$12\$hMPI1uYkP8XqJzI0UPsJwerZ9x.h1KMoUgNPP2cBEoJ18PkjnZoby";

    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
        ]);

        $user->refresh();
        event(new Registered($user));
        return $user;
    }


    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();
        $hashed = $user ? $user->password_hash : self::DUMMY_HASH;
        $correctPassword = Hash::check($data['password'], $hashed);

        if (!$correctPassword || !$user){
            throw new JsonException("email ou senha inválidos", 401);
        }

        if ($user->email_verified_at === null){
            throw new JsonException("Conta não verificada", 401);
        }
        $user->refresh();

        return $user->createToken('login')->plainTextToken;
    }

    public function verifyEmail(int $id, string $hash)
    {
        $user = User::findOrFail($id);
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)){
            throw new JsonException("validação inválida", 403);
        }

        if ($user->hasVerifiedEmail()){
            return response()->json(["message" => "Você já verificou o email anteriormente"]);
        }

        $user->markEmailAsVerified();
        return response()->json(["message" => "Email verificado com sucesso!"]);
    }
}