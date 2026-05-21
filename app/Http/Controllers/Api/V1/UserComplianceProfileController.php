<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserComplianceProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserComplianceProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $profiles = $request->user()
            ->complianceProfiles()
            ->with(['regulator', 'licenseActivity'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $profiles->map(fn ($p) => $this->format($p)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'regulator_id' => 'required|integer|exists:regulators,id',
            'activity_id' => 'required|integer|exists:license_activities,id',
            'notify_days_before' => 'nullable|integer|min:1|max:90',
        ]);

        $profile = $request->user()->complianceProfiles()->updateOrCreate(
            [
                'regulator_id' => $request->regulator_id,
                'license_activity_id' => $request->activity_id,
            ],
            [
                'name' => $request->name,
                'notify_days_before' => $request->integer('notify_days_before', 7),
            ]
        );

        $profile->load(['regulator', 'licenseActivity']);

        return response()->json(['data' => $this->format($profile)], 201);
    }

    public function destroy(Request $request, UserComplianceProfile $userComplianceProfile): JsonResponse
    {
        abort_if($userComplianceProfile->user_id !== $request->user()->id, 403);

        $userComplianceProfile->delete();

        return response()->json(['message' => 'Profile deleted']);
    }

    private function format(UserComplianceProfile $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'notify_days_before' => $p->notify_days_before,
            'created_at' => $p->created_at->toIso8601String(),
            'regulator' => $p->regulator,
            'activity' => [
                'id' => $p->licenseActivity->id,
                'name' => $p->licenseActivity->name,
                'sector' => $p->licenseActivity->sector,
                'suggested_regulator_id' => $p->licenseActivity->suggested_regulator_id,
            ],
        ];
    }
}
