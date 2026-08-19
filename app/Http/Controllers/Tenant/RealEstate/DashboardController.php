<?php

namespace App\Http\Controllers\Tenant\RealEstate;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\JournalEntry;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        $outstandingBills = Bill::where('tenant_id', $tenant->id)
            ->where('status', 'posted')
            ->whereColumn('amount_paid', '<', 'total')
            ->sum(\DB::raw('total - amount_paid'));

        $overdueBills = Bill::where('tenant_id', $tenant->id)
            ->where('status', 'posted')
            ->whereColumn('amount_paid', '<', 'total')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $recentBills = Bill::where('tenant_id', $tenant->id)
            ->orderByDesc('bill_date')
            ->limit(5)
            ->get();

        return view('tenant.real_estate.dashboard', compact(
            'tenant', 'outstandingBills', 'overdueBills', 'recentBills'
        ));
    }
}
