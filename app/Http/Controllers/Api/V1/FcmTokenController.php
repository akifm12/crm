<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserFcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|in:android,ios',
        ]);

        UserFcmToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->string('platform', 'android'),
            ]
        );

        return response()->json(['message' => 'Token registered']);
    }
}
