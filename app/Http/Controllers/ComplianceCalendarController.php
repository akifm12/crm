<?php
// app/Http/Controllers/ComplianceCalendarController.php

namespace App\Http\Controllers;

use App\Models\ComplianceDeadline;
use App\Models\UserDeadline;
use App\Support\SectorConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComplianceCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $sector = $request->query('sector');

        $deadlines = ComplianceDeadline::query()
            ->when($sector, fn ($q) => $q->where(function ($q) use ($sector) {
                $q->where('sector', $sector)->orWhereNull('sector');
            }))
            ->orderByRaw('next_due_date IS NULL, next_due_date asc')
            ->orderBy('sort_order')
            ->get();

        $publicUser = Auth::guard('public')->user();

        return view('public.compliance-calendar', [
            'deadlines'    => $deadlines,
            'sectors'      => SectorConfig::sectors(),
            'sector'       => $sector,
            'publicUser'   => $publicUser,
            'myDeadlines'  => $publicUser ? $publicUser->deadlines()->orderBy('due_date')->get() : collect(),
            'deadlineTypes'=> UserDeadline::typeLabels(),
        ]);
    }
}
