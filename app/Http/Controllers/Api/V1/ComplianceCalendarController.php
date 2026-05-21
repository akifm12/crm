<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ComplianceDeadline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceCalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'regulator_id' => 'required|integer|exists:regulators,id',
            'activity_id' => 'required|integer|exists:license_activities,id',
            'year' => 'nullable|integer|min:2024|max:2030',
        ]);

        $year = $request->integer('year', now()->year);

        $deadlines = ComplianceDeadline::forYear($year)
            ->whereHas('requirement', function ($q) use ($request) {
                $q->active()
                  ->where('regulator_id', $request->regulator_id)
                  ->where(function ($inner) use ($request) {
                      $inner->where('license_activity_id', $request->activity_id)
                            ->orWhereNull('license_activity_id');
                  });
            })
            ->with('requirement.regulator')
            ->orderBy('due_date')
            ->get();

        return response()->json([
            'data' => $deadlines->map(fn ($d) => [
                'id' => $d->id,
                'requirement_id' => $d->requirement_id,
                'due_date' => $d->due_date->toDateString(),
                'title' => $d->title,
                'notes' => $d->notes,
            ]),
        ]);
    }
}
