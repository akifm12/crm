<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\ClientShareholder;
use Illuminate\Http\Request;

class ExpiryController extends Controller
{
    public function index(Request $request)
    {
        $tenant    = app('tenant');
        $tid       = $tenant->id;
        $tab       = $request->input('tab', 'licence');
        $window    = 90; // days ahead to include as "expiring soon"

        $activeIds = BullionClient::where('tenant_id', $tid)
            ->whereIn('status', ['active', 'pending'])
            ->pluck('id');

        $corporateIds = BullionClient::where('tenant_id', $tid)
            ->whereIn('status', ['active', 'pending'])
            ->where('client_type', '!=', 'individual')
            ->pluck('id');

        $data = match ($tab) {

            'licence' => BullionClient::where('tenant_id', $tid)
                ->whereIn('status', ['active', 'pending'])
                ->where(fn($q) => $q->whereNull('trade_license_expiry')
                    ->orWhere('trade_license_expiry', '<=', now()->addDays($window)))
                ->orderByRaw('CASE WHEN trade_license_expiry IS NULL THEN 1 WHEN trade_license_expiry < NOW() THEN 0 ELSE 2 END')
                ->orderBy('trade_license_expiry')
                ->get(),

            'ejari' => BullionClient::where('tenant_id', $tid)
                ->whereIn('status', ['active', 'pending'])
                ->where('client_type', '!=', 'individual')
                ->where(fn($q) => $q->whereNull('ejari_expiry')
                    ->orWhere('ejari_expiry', '<=', now()->addDays($window)))
                ->orderByRaw('CASE WHEN ejari_expiry IS NULL THEN 1 WHEN ejari_expiry < NOW() THEN 0 ELSE 2 END')
                ->orderBy('ejari_expiry')
                ->get(),

            'eid' => ClientShareholder::whereIn('bullion_client_id', $activeIds)
                ->where('is_resident', true)
                ->where(fn($q) => $q->whereNull('eid_expiry')
                    ->orWhere('eid_expiry', '<=', now()->addDays($window)))
                ->with('client')
                ->orderByRaw('CASE WHEN eid_expiry IS NULL THEN 1 WHEN eid_expiry < NOW() THEN 0 ELSE 2 END')
                ->orderBy('eid_expiry')
                ->get(),

            'passport' => ClientShareholder::whereIn('bullion_client_id', $activeIds)
                ->whereNotNull('passport_expiry')
                ->where('passport_expiry', '<=', now()->addDays($window))
                ->with('client')
                ->orderByRaw('CASE WHEN passport_expiry < NOW() THEN 0 ELSE 1 END')
                ->orderBy('passport_expiry')
                ->get(),

            'docs' => ClientDocument::where('tenant_id', $tid)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays($window))
                ->with('client')
                ->orderByRaw('CASE WHEN expiry_date < NOW() THEN 0 ELSE 1 END')
                ->orderBy('expiry_date')
                ->get(),

            default => collect(),
        };

        // Tab counts for badges
        $counts = [
            'licence'  => BullionClient::where('tenant_id', $tid)->whereIn('status', ['active','pending'])
                ->where(fn($q) => $q->whereNull('trade_license_expiry')->orWhere('trade_license_expiry', '<=', now()->addDays($window)))->count(),
            'ejari'    => BullionClient::where('tenant_id', $tid)->whereIn('status', ['active','pending'])->where('client_type','!=','individual')
                ->where(fn($q) => $q->whereNull('ejari_expiry')->orWhere('ejari_expiry', '<=', now()->addDays($window)))->count(),
            'eid'      => ClientShareholder::whereIn('bullion_client_id', $activeIds)->where('is_resident',true)
                ->where(fn($q) => $q->whereNull('eid_expiry')->orWhere('eid_expiry', '<=', now()->addDays($window)))->count(),
            'passport' => ClientShareholder::whereIn('bullion_client_id', $activeIds)->whereNotNull('passport_expiry')
                ->where('passport_expiry', '<=', now()->addDays($window))->count(),
            'docs'     => ClientDocument::where('tenant_id', $tid)->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays($window))->count(),
        ];

        return view('tenant.expiry', compact('tenant', 'tab', 'data', 'counts', 'window'));
    }
}
