<?php
// app/Http/Controllers/Api/DeadlineController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserDeadlineRequest;
use App\Http\Resources\UserDeadlineResource;
use App\Models\UserDeadline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeadlineController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return UserDeadlineResource::collection(
            $request->user()->deadlines()->orderBy('due_date')->get()
        );
    }

    public function store(UserDeadlineRequest $request): JsonResponse
    {
        $deadline = $request->user()->deadlines()->create($request->validated());

        return response()->json(new UserDeadlineResource($deadline), 201);
    }

    public function update(UserDeadlineRequest $request, UserDeadline $deadline): JsonResponse
    {
        abort_unless($deadline->public_user_id === $request->user()->id, 403);

        $deadline->update($request->validated());

        return response()->json(new UserDeadlineResource($deadline));
    }

    public function destroy(Request $request, UserDeadline $deadline): JsonResponse
    {
        abort_unless($deadline->public_user_id === $request->user()->id, 403);

        $deadline->delete();

        return response()->json(['message' => 'Deadline removed.']);
    }
}
