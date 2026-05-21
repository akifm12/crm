<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ComplianceRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplianceRequirementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'regulator_id' => 'required|integer|exists:regulators,id',
            'activity_id' => 'required|integer|exists:license_activities,id',
        ]);

        $requirements = ComplianceRequirement::active()
            ->where('regulator_id', $request->regulator_id)
            ->where(function ($q) use ($request) {
                $q->where('license_activity_id', $request->activity_id)
                  ->orWhereNull('license_activity_id');
            })
            ->with(['regulator', 'deadlines' => fn ($q) => $q->upcoming()->orderBy('due_date')])
            ->orderBy('frequency')
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $requirements->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'description' => $r->description,
                'regulator_id' => $r->regulator_id,
                'regulator_name' => $r->regulator->name,
                'regulator_acronym' => $r->regulator->acronym,
                'license_activity_id' => $r->license_activity_id,
                'frequency' => $r->frequency,
                'category' => $r->category,
                'submission_channel' => $r->submission_channel,
                'penalty_note' => $r->penalty_note,
                'upcoming_deadlines' => $r->deadlines->map(fn ($d) => [
                    'id' => $d->id,
                    'requirement_id' => $d->requirement_id,
                    'due_date' => $d->due_date->toDateString(),
                    'title' => $d->title,
                    'notes' => $d->notes,
                ]),
            ]),
        ]);
    }
}
