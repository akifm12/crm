<?php
// app/Http/Controllers/Tenant/DashboardController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');

        $clients = BullionClient::where('tenant_id', $tenant->id);

        $stats = [
            'total'     => (clone $clients)->count(),
            'active'    => (clone $clients)->where('status', 'active')->count(),
            'pending'   => (clone $clients)->where('status', 'pending')->count(),
            'high_risk' => (clone $clients)->where('risk_rating', 'high')->count(),

            // Expiring within 30 days
            'expiring_soon' => (clone $clients)
                ->whereNotNull('trade_license_expiry')
                ->whereBetween('trade_license_expiry', [now(), now()->addDays(30)])
                ->count(),

            // Already expired
            'expired' => (clone $clients)
                ->whereNotNull('trade_license_expiry')
                ->where('trade_license_expiry', '<', now())
                ->count(),

            // KYC review due (past due or within 30 days)
            'review_due' => (clone $clients)
                ->whereNotNull('next_review_date')
                ->where('next_review_date', '<=', now()->addDays(30))
                ->count(),

            // Not yet screened
            'unscreened' => (clone $clients)
                ->where('screening_status', 'not_screened')
                ->count(),
        ];

        // Recent additions
        $recent = BullionClient::where('tenant_id', $tenant->id)
            ->latest()
            ->take(8)
            ->get();

        // Expiry alerts (next 60 days)
        $expiry_alerts = BullionClient::where('tenant_id', $tenant->id)
            ->whereNotNull('trade_license_expiry')
            ->where('trade_license_expiry', '<=', now()->addDays(60))
            ->orderBy('trade_license_expiry')
            ->take(5)
            ->get();

        // Review due alerts
        $review_alerts = BullionClient::where('tenant_id', $tenant->id)
            ->whereNotNull('next_review_date')
            ->where('next_review_date', '<=', now()->addDays(30))
            ->orderBy('next_review_date')
            ->take(5)
            ->get();

        return view('tenant.dashboard', compact(
            'tenant', 'stats', 'recent', 'expiry_alerts', 'review_alerts'
        ));
    }
}
