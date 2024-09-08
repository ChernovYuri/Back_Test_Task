<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // Регистрация пользователя
    public function register(Request $request)
    {
        $validated_user = $request->validate([
            'last_name' => 'required|string|max:40',
            'name' => 'required|string|max:40',
            'middle_name' => 'string|max:40',
            'email' => 'required|string|email|max:80|unique:users',
            'phone' => 'required|string|email|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->authService->registerUser($validated_user);

        return response()->json(['user' => $user], 201);
    }

    // Авторизация пользователя
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    // Сброс пароля
    public function resetPassword(Request $request)
    {
        $validated_user = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required',
        ]);

        $response = $this->authService->resetUserPassword($validated_user);

        if ($response === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset'], 200);
        }

        return response()->json(['message' => 'Failed to reset password'], 400);
    }
}
