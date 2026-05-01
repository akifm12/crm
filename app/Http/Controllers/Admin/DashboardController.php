<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmClient;
use App\Models\CrmSla;
use App\Models\CrmTask;
use App\Models\CrmNote;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Top stats ──────────────────────────────────────────────────────
        $stats = [
            'total_clients'   => CrmClient::where('status', 'active')->count(),
            'active_slas'     => CrmSla::where('status', 'active')->count(),
            'open_tasks'      => CrmTask::whereIn('status', ['pending', 'in_progress'])->count(),
            'overdue_tasks'   => CrmTask::whereIn('status', ['pending', 'in_progress'])
                                    ->whereNotNull('due_date')
                                    ->where('due_date', '<', now())
                                    ->count(),

            // Licences expiring within 30 days
            'expiring_soon'   => CrmClient::whereNotNull('license_expiry')
                                    ->whereBetween('license_expiry', [now(), now()->addDays(30)])
                                    ->count(),

            // Already expired
            'expired_licences'=> CrmClient::whereNotNull('license_expiry')
                                    ->where('license_expiry', '<', now())
                                    ->where('status', 'active')
                                    ->count(),

            // Active clients with no active SLA
            'no_sla'          => CrmClient::where('status', 'active')
                                    ->whereDoesntHave('slas', fn($q) => $q->where('status', 'active'))
                                    ->count(),

            // Active tenants
            'active_portals'  => Tenant::where('is_active', true)->count(),
        ];

        // ── Pipeline counts ────────────────────────────────────────────────
        $pipeline = CrmClient::selectRaw('stage, count(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');

        // ── Expired licence alert list ─────────────────────────────────────
        $expired = CrmClient::where('status', 'active')
            ->whereNotNull('license_expiry')
            ->where('license_expiry', '<', now())
            ->orderBy('license_expiry')
            ->take(8)
            ->get(['id', 'company_name', 'license_expiry', 'license_number']);

        // ── Expiring soon list ─────────────────────────────────────────────
        $expiring = CrmClient::whereNotNull('license_expiry')
            ->whereBetween('license_expiry', [now(), now()->addDays(30)])
            ->orderBy('license_expiry')
            ->take(8)
            ->get(['id', 'company_name', 'license_expiry', 'license_number']);

        // ── Overdue tasks ──────────────────────────────────────────────────
        $overdue_tasks = CrmTask::with(['client', 'assignee'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->take(8)
            ->get();

        // ── Clients with no active SLA ─────────────────────────────────────
        $no_sla_clients = CrmClient::where('status', 'active')
            ->whereDoesntHave('slas', fn($q) => $q->where('status', 'active'))
            ->whereIn('stage', ['active', 'onboarding'])
            ->orderBy('company_name')
            ->take(8)
            ->get(['id', 'company_name', 'stage']);

        // ── Recent activity ────────────────────────────────────────────────
        $recent_activity = CrmNote::with(['client', 'author'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'pipeline', 'expired', 'expiring',
            'overdue_tasks', 'no_sla_clients', 'recent_activity'
        ));
    }
}
