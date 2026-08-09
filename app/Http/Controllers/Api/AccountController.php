<?php
// app/Http/Controllers/Api/AccountController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json(new PublicUserResource($request->user()));
    }
}
