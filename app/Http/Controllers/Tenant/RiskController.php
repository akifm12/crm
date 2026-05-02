<?php
// app/Http/Controllers/Tenant/RiskController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    // ── Risk overview ──────────────────────────────────────────────────────

    public function index()
    {
        $tenant = app('tenant');

        $clients = BullionClient::where('tenant_id', $tenant->id)
            ->orderByRaw("FIELD(risk_rating, 'high', 'medium', 'low') ASC")
            ->orderBy('updated_at', 'desc')
            ->get();

        $stats = [
            'high'       => $clients->where('risk_rating', 'high')->count(),
            'medium'     => $clients->where('risk_rating', 'medium')->count(),
            'low'        => $clients->where('risk_rating', 'low')->count(),
            'unrated'    => $clients->whereNull('risk_rating')->count(),
            'review_due' => $clients->filter(fn($c) => $c->isReviewDue())->count(),
            'edd'        => $clients->where('cdd_type', 'enhanced')->count(),
        ];

        return view('tenant.risk', compact('tenant', 'clients', 'stats'));
    }

    // ── Individual risk assessment form ────────────────────────────────────

    public function assess(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        return view('tenant.risk_assess', compact('tenant', 'client'));
    }

    // ── Save risk assessment ───────────────────────────────────────────────

    public function saveAssessment(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $factors = $request->input('factors', []);

        // Calculate weighted score
        $weights = [
            'customer'     => 0.30,
            'geographic'   => 0.25,
            'product'      => 0.20,
            'transaction'  => 0.15,
            'channel'      => 0.05,
            'supply_chain' => 0.05,
        ];

        $categoryScores = [];
        $weightedTotal  = 0;
        $totalWeight    = 0;

        foreach ($weights as $category => $weight) {
            $catFactors = array_filter($factors, fn($k) => str_starts_with($k, $category . '_'), ARRAY_FILTER_USE_KEY);
            if (empty($catFactors)) continue;
            $avg = array_sum($catFactors) / count($catFactors);
            $categoryScores[$category] = round($avg, 2);
            $weightedTotal += $avg * $weight;
            $totalWeight   += $weight;
        }

        $overallScore = $totalWeight > 0 ? round($weightedTotal / $totalWeight, 2) : 0;

        // Determine suggested rating
        $suggestedRating = match(true) {
            $overallScore >= 2.4 => 'high',
            $overallScore >= 1.7 => 'medium',
            default              => 'low',
        };

        // Use override if provided, otherwise use suggested
        $finalRating = $request->input('rating_override') ?: $suggestedRating;

        // Determine CDD type
        $cddType = $finalRating === 'high' ? 'enhanced' : 'standard';

        $assessmentData = [
            'factors'          => $factors,
            'category_scores'  => $categoryScores,
            'overall_score'    => $overallScore,
            'suggested_rating' => $suggestedRating,
            'final_rating'     => $finalRating,
            'override_reason'  => $request->input('override_reason'),
            'assessed_by'      => auth()->user()->name,
            'assessed_at'      => now()->toDateTimeString(),
        ];

        $client->update([
            'risk_rating'          => $finalRating,
            'cdd_type'             => $cddType,
            'risk_notes'           => $request->input('risk_notes'),
            'next_review_date'     => $request->input('next_review_date'),
            'risk_assessment_data' => $assessmentData,
            'risk_assessed_at'     => now(),
            'risk_assessed_by'     => auth()->id(),
        ]);

        return redirect()
            ->route('tenant.clients.show', [$slug, $client->id])
            ->with('success', "Risk assessment saved — {$client->displayName()} rated as " . strtoupper($finalRating) . '.');
    }
}
