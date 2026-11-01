<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FixedAssetController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $assets = FixedAsset::where('tenant_id', $tenant->id)
            ->with('category')
            ->where('status', '!=', 'disposed')
            ->orderBy('asset_number')
            ->orderBy('name')
            ->get();

        $totalCost  = $assets->sum('purchase_cost');
        $totalAccum = $assets->sum('accumulated_depreciation');
        $totalNBV   = $assets->sum('net_book_value');

        return view('tenant.accounting.assets.index', compact('tenant', 'assets', 'totalCost', 'totalAccum', 'totalNBV'));
    }

    public function create()
    {
        $tenant     = app('tenant');
        $categories = AssetCategory::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $accounts   = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('tenant.accounting.assets.create', compact('tenant', 'categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'name'              => 'required|string|max:255',
            'purchase_date'     => 'required|date',
            'purchase_cost'     => 'required|numeric|min:0',
            'category_id'       => 'nullable|exists:asset_categories,id',
            'useful_life_months'=> 'nullable|numeric|min:1',
            'salvage_value'     => 'nullable|numeric|min:0',
        ]);

        $cost     = (float) $request->purchase_cost;
        $salvage  = (float) ($request->salvage_value ?? 0);
        $lifeMonths = (float) ($request->useful_life_months ?? 60);

        $count       = FixedAsset::where('tenant_id', $tenant->id)->count() + 1;
        $assetNumber = 'FA-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        FixedAsset::create([
            'tenant_id'          => $tenant->id,
            'asset_number'       => $assetNumber,
            'name'               => $request->name,
            'category_id'        => $request->category_id,
            'purchase_date'      => $request->purchase_date,
            'purchase_cost'      => $cost,
            'salvage_value'      => $salvage,
            'useful_life_months' => $lifeMonths,
            'depreciation_method'=> $request->depreciation_method ?? 'straight_line',
            'net_book_value'     => $cost,
            'notes'              => $request->notes,
        ]);

        return redirect()->route('tenant.accounting.assets.index', $tenant->slug)
            ->with('success', 'Fixed asset registered.');
    }

    public function show(string $slug, FixedAsset $fixedAsset)
    {
        $tenant = app('tenant');
        abort_if($fixedAsset->tenant_id !== $tenant->id, 404);

        return view('tenant.accounting.assets.show', compact('tenant', 'fixedAsset'));
    }

    public function runDepreciation(Request $request, string $slug, FixedAsset $fixedAsset)
    {
        $tenant = app('tenant');
        abort_if($fixedAsset->tenant_id !== $tenant->id, 404);

        $request->validate(['periods' => 'required|integer|min:1|max:120']);

        $periods   = (int) $request->periods;
        $monthly   = $fixedAsset->monthlyDepreciation();
        $depAmount = min($monthly * $periods, $fixedAsset->net_book_value);

        if ($depAmount <= 0) {
            return back()->with('error', 'Asset is already fully depreciated.');
        }

        $newAccum = $fixedAsset->accumulated_depreciation + $depAmount;
        $newNBV   = $fixedAsset->purchase_cost - $newAccum;

        $fixedAsset->update([
            'accumulated_depreciation' => $newAccum,
            'net_book_value'           => max($newNBV, $fixedAsset->salvage_value),
            'last_depreciation_date'   => Carbon::now()->toDateString(),
            'status'                   => $newNBV <= $fixedAsset->salvage_value ? 'fully_depreciated' : 'active',
        ]);

        return back()->with('success', "Depreciation of AED " . number_format($depAmount, 2) . " recorded for {$periods} period(s).");
    }

    public function dispose(Request $request, string $slug, FixedAsset $fixedAsset)
    {
        $tenant = app('tenant');
        abort_if($fixedAsset->tenant_id !== $tenant->id, 404);

        $fixedAsset->update(['status' => 'disposed', 'notes' => trim($fixedAsset->notes . "\nDisposed: " . now()->format('d M Y'))]);

        return back()->with('success', 'Asset marked as disposed.');
    }
}
