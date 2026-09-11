<?php

namespace App\Http\Controllers;

use App\Domain\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\NewUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(Private AuthService $service)
    {
        
    }

    public function register(NewUserRequest $request)
    {
        $data = $request->validated();
        $user = $this->service->register($data);
        return new UserResource($user);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $token = $this->service->login($data);
        return response()->json(["token" => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "deslogado com sucesso"]);
    }

    public function verify(Request $request, int $id, string $hash)
    {
        $verify = $this->service->verifyEmail($id, $hash);

        return response()->json(["message" => "email verificado com sucesso!"]);
    }

    
}
