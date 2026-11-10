<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $email = Str::lower(trim($credentials['email']));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            static $dummyHash;
            $dummyHash ??= Hash::make(Str::random(48));
            Hash::check($credentials['password'], $dummyHash);
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'This account is inactive.',
                'code' => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        if ($request->attributes->get('sanctum')) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Authenticated successfully.',
                'user' => UserResource::make($user)->resolve($request),
            ]);
        }

        $token = $user->createToken('api-login', ['*'], now()->addDay());

        return response()->json([
            'message' => 'Authenticated successfully.',
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'user' => UserResource::make($user)->resolve($request),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $accessToken = $user?->currentAccessToken();

        if ($accessToken instanceof PersonalAccessToken) {
            $accessToken->delete();
        } elseif ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => UserResource::make($request->user())->resolve($request),
        ]);
    }
}
