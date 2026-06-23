<?php
// app/Http/Controllers/Tenant/DashboardController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\ClientShareholder;
use App\Models\GoamlReport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant    = app('tenant');
        $tid        = $tenant->id;
        $base       = BullionClient::where('tenant_id', $tid);
        $activeBase = BullionClient::where('tenant_id', $tid)->whereIn('status', ['active', 'pending']);
        $clientIds  = (clone $activeBase)->pluck('id');

        // ── Core stats ──────────────────────────────────────────────────────
        $total   = (clone $base)->count();
        $active  = (clone $base)->where('status', 'active')->count();
        $pending = (clone $base)->where('status', 'pending')->count();

        // ── Risk breakdown ───────────────────────────────────────────────────
        $riskHigh    = (clone $base)->where('risk_rating', 'high')->count();
        $riskMedium  = (clone $base)->where('risk_rating', 'medium')->count();
        $riskLow     = (clone $base)->where('risk_rating', 'low')->count();
        $riskUnrated = (clone $base)->whereNull('risk_rating')->count();

        // ── Compliance alerts ─────────────────────────────────────────────────
        $licenceMissing  = (clone $activeBase)->where('client_type', '!=', 'individual')->whereNull('trade_license_expiry')->count();
        $licenceExpired  = (clone $activeBase)->where('client_type', '!=', 'individual')->whereNotNull('trade_license_expiry')->where('trade_license_expiry', '<', now())->count();
        $licenceExpiring = (clone $activeBase)->where('client_type', '!=', 'individual')->whereNotNull('trade_license_expiry')->whereBetween('trade_license_expiry', [now(), now()->addDays(30)])->count();
        $ejariMissing    = (clone $activeBase)->where('client_type', '!=', 'individual')->whereNull('ejari_expiry')->count();
        $ejariExpired    = (clone $activeBase)->where('client_type', '!=', 'individual')->whereNotNull('ejari_expiry')->where('ejari_expiry', '<', now())->count();
        $ejariExpiring   = (clone $activeBase)->where('client_type', '!=', 'individual')->whereNotNull('ejari_expiry')->whereBetween('ejari_expiry', [now(), now()->addDays(30)])->count();
        $reviewOverdue   = (clone $base)->whereNotNull('next_review_date')->where('next_review_date', '<', now())->count();
        $reviewDueSoon   = (clone $activeBase)->whereNotNull('next_review_date')->whereBetween('next_review_date', [now(), now()->addDays(30)])->count();
        $unscreened      = (clone $activeBase)->where('screening_status', 'not_screened')->count();
        $screeningMatch  = (clone $activeBase)->where('screening_status', 'match')->count();
        $edd             = (clone $base)->where('cdd_type', 'enhanced')->count();

        // ── Document alerts ───────────────────────────────────────────────────
        $docsExpired  = ClientDocument::where('tenant_id', $tid)->where('expiry_date', '<', now())->count();
        $docsExpiring = ClientDocument::where('tenant_id', $tid)->whereBetween('expiry_date', [now(), now()->addDays(30)])->count();

        // ── Shareholder EID alerts ─────────────────────────────────────────────
        $eidExpired  = ClientShareholder::whereIn('bullion_client_id', $clientIds)
            ->where('is_resident', true)->whereNotNull('eid_expiry')
            ->where('eid_expiry', '<', now())->count();
        $eidExpiring = ClientShareholder::whereIn('bullion_client_id', $clientIds)
            ->where('is_resident', true)->whereNotNull('eid_expiry')
            ->whereBetween('eid_expiry', [now(), now()->addDays(30)])->count();

        // ── Shareholder passport alerts ────────────────────────────────────────
        $passportExpired  = ClientShareholder::whereIn('bullion_client_id', $clientIds)
            ->whereNotNull('passport_expiry')
            ->where('passport_expiry', '<', now())->count();
        $passportExpiring = ClientShareholder::whereIn('bullion_client_id', $clientIds)
            ->whereNotNull('passport_expiry')
            ->whereBetween('passport_expiry', [now(), now()->addDays(60)])->count();

        // ── goAML ─────────────────────────────────────────────────────────────
        $goamlTotal = GoamlReport::where('tenant_id', $tid)->count();
        $goamlMonth = GoamlReport::where('tenant_id', $tid)->where('created_at', '>=', now()->startOfMonth())->count();

        // ── Type breakdown ───────────────────────────────────────────────────
        $typeBreakdown = BullionClient::where('tenant_id', $tid)
            ->selectRaw('client_type, count(*) as count')
            ->groupBy('client_type')
            ->pluck('count', 'client_type')
            ->toArray();

        $stats = compact(
            'total', 'active', 'pending',
            'riskHigh', 'riskMedium', 'riskLow', 'riskUnrated',
            'licenceMissing', 'licenceExpired', 'licenceExpiring',
            'ejariMissing', 'ejariExpired', 'ejariExpiring',
            'eidExpired', 'eidExpiring',
            'passportExpired', 'passportExpiring',
            'reviewOverdue', 'reviewDueSoon',
            'unscreened', 'screeningMatch', 'edd',
            'docsExpired', 'docsExpiring',
            'goamlTotal', 'goamlMonth',
            'typeBreakdown'
        );

        // ── Alert lists ───────────────────────────────────────────────────────
        $expiry_alerts = BullionClient::where('tenant_id', $tid)->whereIn('status', ['active', 'pending'])
            ->where(fn($q) => $q->whereNull('trade_license_expiry')
                ->orWhere('trade_license_expiry', '<=', now()->addDays(60)))
            ->orderByRaw('CASE WHEN trade_license_expiry IS NULL THEN 1 WHEN trade_license_expiry < NOW() THEN 0 ELSE 2 END')
            ->orderBy('trade_license_expiry')
            ->take(10)->get();

        $ejari_alerts = BullionClient::where('tenant_id', $tid)
            ->whereIn('status', ['active', 'pending'])
            ->where('client_type', '!=', 'individual')
            ->where(fn($q) => $q->whereNull('ejari_expiry')
                ->orWhere('ejari_expiry', '<=', now()->addDays(60)))
            ->orderByRaw('CASE WHEN ejari_expiry IS NULL THEN 1 WHEN ejari_expiry < NOW() THEN 0 ELSE 2 END')
            ->orderBy('ejari_expiry')
            ->take(10)->get();

        $eid_alerts = ClientShareholder::whereIn('bullion_client_id', $clientIds)
            ->where('is_resident', true)->whereNotNull('eid_expiry')
            ->where('eid_expiry', '<=', now()->addDays(60))
            ->with('client')->orderBy('eid_expiry')->take(8)->get();

        $passport_alerts = ClientShareholder::whereIn('bullion_client_id', $clientIds)
            ->whereNotNull('passport_expiry')
            ->where('passport_expiry', '<=', now()->addDays(60))
            ->with('client')->orderBy('passport_expiry')->take(8)->get();

        $review_alerts = BullionClient::where('tenant_id', $tid)
            ->whereNotNull('next_review_date')
            ->where('next_review_date', '<=', now()->addDays(30))
            ->orderBy('next_review_date')->take(6)->get();

        $doc_alerts = ClientDocument::where('tenant_id', $tid)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->with('client')->orderBy('expiry_date')->take(6)->get();

        $recent = BullionClient::where('tenant_id', $tid)->latest()->take(6)->get();

        $recent_goaml = GoamlReport::where('tenant_id', $tid)
            ->with('client')->latest()->take(5)->get();

        return view('tenant.dashboard', compact(
            'tenant', 'stats',
            'expiry_alerts', 'ejari_alerts', 'eid_alerts', 'passport_alerts',
            'review_alerts', 'doc_alerts',
            'recent', 'recent_goaml'
        ));
    }
}
