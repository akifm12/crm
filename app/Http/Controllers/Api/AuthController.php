<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ThrottlesPublicLogins;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicUserResource;
use App\Models\PublicUser;
use App\Services\MailerSubscriberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ThrottlesPublicLogins;

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:public_users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $subscribe = $request->boolean('subscribed_to_updates', true);

        $user = PublicUser::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'subscribed_to_updates' => $subscribe,
        ]);

        if ($subscribe) {
            app(MailerSubscriberService::class)->subscribe($user->email, $user->name);
        }

        return response()->json([
            'user' => new PublicUserResource($user),
            'token' => $user->createToken('bluearrow-app')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->attemptPublicLogin($request);

        return response()->json([
            'user' => new PublicUserResource($user),
            'token' => $user->createToken('bluearrow-app')->plainTextToken,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
