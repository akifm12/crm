<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\ChartOfAccount;
use App\Models\DepreciationLine;
use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
{
    public function index(string $slug)
    {
        $tenant     = app('tenant');
        $assets     = FixedAsset::where('tenant_id', $tenant->id)
            ->where('is_deleted', false)
            ->with('category')
            ->orderBy('asset_number')
            ->get();
        $categories = AssetCategory::where('tenant_id', $tenant->id)->orderBy('name')->get();

        $totalCost  = $assets->sum('purchase_cost');
        $totalAccum = $assets->sum('accumulated_depreciation');
        $totalNbv   = $assets->sum('net_book_value');

        return view('tenant.accounting.assets.index', compact('tenant', 'assets', 'categories', 'totalCost', 'totalAccum', 'totalNbv'));
    }

    public function create(string $slug)
    {
        $tenant     = app('tenant');
        $categories = AssetCategory::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $suppliers  = Supplier::where('tenant_id', $tenant->id)->where('is_deleted', false)->orderBy('name')->get();
        $accounts   = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->groupBy('account_type');

        return view('tenant.accounting.assets.create', compact('tenant', 'categories', 'suppliers', 'accounts'));
    }

    public function store(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'name'                   => 'required|string|max:255',
            'purchase_date'          => 'required|date',
            'purchase_cost'          => 'required|numeric|min:0',
            'asset_account_id'       => 'required|integer',
            'accum_depr_account_id'  => 'required|integer',
            'depr_expense_account_id'=> 'required|integer',
        ]);

        $cost        = floatval($request->purchase_cost);
        $residual    = floatval($request->residual_value ?? 0);
        $depreciable = $cost - $residual;
        $life        = floatval($request->useful_life_years ?? 5);
        $rate        = $life > 0 ? (1 / $life) : 0.2;

        $count       = FixedAsset::where('tenant_id', $tenant->id)->count() + 1;
        $assetNumber = 'FA-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        FixedAsset::create([
            'tenant_id'               => $tenant->id,
            'category_id'             => $request->category_id ?: null,
            'asset_number'            => $assetNumber,
            'name'                    => $request->name,
            'description'             => $request->description,
            'serial_number'           => $request->serial_number,
            'location'                => $request->location,
            'assigned_to'             => $request->assigned_to,
            'purchase_date'           => $request->purchase_date,
            'in_service_date'         => $request->in_service_date ?? $request->purchase_date,
            'purchase_cost'           => $cost,
            'residual_value'          => $residual,
            'depreciable_amount'      => $depreciable,
            'depreciation_method'     => $request->depreciation_method ?? 'straight_line',
            'useful_life_years'       => $life,
            'depreciation_rate'       => $rate,
            'accumulated_depreciation'=> 0,
            'net_book_value'          => $cost,
            'asset_account_id'        => $request->asset_account_id,
            'accum_depr_account_id'   => $request->accum_depr_account_id,
            'depr_expense_account_id' => $request->depr_expense_account_id,
            'status'                  => 'active',
            'supplier_id'             => $request->supplier_id ?: null,
            'purchase_invoice_ref'    => $request->purchase_invoice_ref,
            'insurance_policy_no'     => $request->insurance_policy_no,
            'insurance_expiry'        => $request->insurance_expiry ?: null,
            'notes'                   => $request->notes,
        ]);

        return redirect()->route('tenant.accounting.assets.index', $slug)
            ->with('success', "Asset {$assetNumber} created.");
    }

    public function show(string $slug, FixedAsset $asset)
    {
        $tenant = app('tenant');
        abort_if($asset->tenant_id !== $tenant->id, 403);
        $asset->load('category', 'supplier', 'assetAccount', 'accumDeprAccount', 'deprExpAccount', 'depreciationLines');
        return view('tenant.accounting.assets.show', compact('tenant', 'asset'));
    }

    public function edit(string $slug, FixedAsset $asset)
    {
        $tenant     = app('tenant');
        abort_if($asset->tenant_id !== $tenant->id, 403);
        $categories = AssetCategory::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $suppliers  = Supplier::where('tenant_id', $tenant->id)->where('is_deleted', false)->orderBy('name')->get();
        $accounts   = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('code')->get()->groupBy('account_type');
        return view('tenant.accounting.assets.edit', compact('tenant', 'asset', 'categories', 'suppliers', 'accounts'));
    }

    public function update(Request $request, string $slug, FixedAsset $asset)
    {
        $tenant = app('tenant');
        abort_if($asset->tenant_id !== $tenant->id, 403);
        $request->validate([
            'name'         => 'required|string|max:255',
            'purchase_date'=> 'required|date',
        ]);

        $asset->update([
            'category_id'          => $request->category_id ?: null,
            'name'                 => $request->name,
            'description'          => $request->description,
            'serial_number'        => $request->serial_number,
            'location'             => $request->location,
            'assigned_to'          => $request->assigned_to,
            'in_service_date'      => $request->in_service_date ?? $asset->in_service_date,
            'supplier_id'          => $request->supplier_id ?: null,
            'purchase_invoice_ref' => $request->purchase_invoice_ref,
            'insurance_policy_no'  => $request->insurance_policy_no,
            'insurance_expiry'     => $request->insurance_expiry ?: null,
            'notes'                => $request->notes,
        ]);

        return back()->with('success', 'Asset updated.');
    }

    public function storeCategory(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate(['name' => 'required|string|max:100']);

        AssetCategory::create([
            'tenant_id'               => $tenant->id,
            'name'                    => $request->name,
            'depreciation_method'     => $request->depreciation_method ?? 'straight_line',
            'useful_life_years'       => $request->useful_life_years ?? 5,
            'residual_value_percent'  => $request->residual_value_percent ?? 0,
            'asset_account_id'        => $request->asset_account_id ?: null,
            'accum_depr_account_id'   => $request->accum_depr_account_id ?: null,
            'depr_expense_account_id' => $request->depr_expense_account_id ?: null,
        ]);

        return back()->with('success', 'Category created.');
    }

    public function runDepreciation(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate(['period_end' => 'required|date']);

        $periodEnd   = Carbon::parse($request->period_end)->endOfMonth();
        $periodStart = $periodEnd->copy()->startOfMonth();
        $posted      = 0;

        DB::transaction(function () use ($tenant, $periodStart, $periodEnd, &$posted) {
            $assets = FixedAsset::where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->where('is_deleted', false)
                ->where('net_book_value', '>', 0)
                ->get();

            foreach ($assets as $asset) {
                $alreadyRan = DepreciationLine::where('asset_id', $asset->id)
                    ->where('period_end', $periodEnd->toDateString())
                    ->exists();
                if ($alreadyRan) continue;

                $monthlyDepr = round($asset->depreciable_amount / ($asset->useful_life_years * 12), 4);
                $monthlyDepr = min($monthlyDepr, $asset->net_book_value);
                if ($monthlyDepr <= 0) continue;

                $newAccum = $asset->accumulated_depreciation + $monthlyDepr;
                $newNbv   = $asset->purchase_cost - $newAccum;

                // Journal entry
                $count = JournalEntry::where('tenant_id', $tenant->id)->count() + 1;
                $je    = JournalEntry::create([
                    'tenant_id'    => $tenant->id,
                    'entry_number' => 'JE-' . str_pad($count, 5, '0', STR_PAD_LEFT),
                    'entry_date'   => $periodEnd->toDateString(),
                    'journal_type' => 'GENERAL',
                    'reference'    => $asset->asset_number,
                    'description'  => 'Depreciation — ' . $asset->name . ' ' . $periodEnd->format('M Y'),
                    'status'       => 'POSTED',
                    'total_debit'  => $monthlyDepr,
                    'total_credit' => $monthlyDepr,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id'       => $asset->depr_expense_account_id,
                    'description'      => 'Depreciation ' . $asset->asset_number,
                    'debit_amount'     => $monthlyDepr,
                    'credit_amount'    => 0,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id'       => $asset->accum_depr_account_id,
                    'description'      => 'Accum. depreciation ' . $asset->asset_number,
                    'debit_amount'     => 0,
                    'credit_amount'    => $monthlyDepr,
                ]);

                DepreciationLine::create([
                    'tenant_id'               => $tenant->id,
                    'asset_id'                => $asset->id,
                    'period_start'            => $periodStart->toDateString(),
                    'period_end'              => $periodEnd->toDateString(),
                    'depreciation_amount'     => $monthlyDepr,
                    'accumulated_depreciation'=> $newAccum,
                    'net_book_value'          => max(0, $newNbv),
                    'journal_entry_id'        => $je->id,
                ]);

                $asset->update([
                    'accumulated_depreciation' => $newAccum,
                    'net_book_value'           => max(0, $newNbv),
                    'status'                   => $newNbv <= 0 ? 'fully_depreciated' : 'active',
                ]);

                $posted++;
            }
        });

        return back()->with('success', "Depreciation posted for {$posted} asset(s).");
    }

    public function dispose(Request $request, string $slug, FixedAsset $asset)
    {
        $tenant = app('tenant');
        abort_if($asset->tenant_id !== $tenant->id, 403);
        abort_if($asset->status === 'disposed', 422);

        $request->validate([
            'disposal_date'       => 'required|date',
            'proceeds_account_id' => 'nullable|integer',
            'proceeds_amount'     => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $tenant, $asset) {
            $proceeds = floatval($request->proceeds_amount ?? 0);
            $gain     = $proceeds - $asset->net_book_value;

            $count = JournalEntry::where('tenant_id', $tenant->id)->count() + 1;
            $je    = JournalEntry::create([
                'tenant_id'    => $tenant->id,
                'entry_number' => 'JE-' . str_pad($count, 5, '0', STR_PAD_LEFT),
                'entry_date'   => $request->disposal_date,
                'journal_type' => 'GENERAL',
                'reference'    => $asset->asset_number,
                'description'  => 'Asset disposal — ' . $asset->name,
                'status'       => 'POSTED',
                'total_debit'  => $asset->purchase_cost + ($proceeds > 0 ? 0 : 0),
                'total_credit' => $asset->purchase_cost,
            ]);

            // Remove asset at cost
            JournalLine::create(['journal_entry_id' => $je->id, 'account_id' => $asset->accum_depr_account_id,
                'description' => 'Remove accum. depr.', 'debit_amount' => $asset->accumulated_depreciation, 'credit_amount' => 0]);
            JournalLine::create(['journal_entry_id' => $je->id, 'account_id' => $asset->asset_account_id,
                'description' => 'Remove asset at cost', 'debit_amount' => 0, 'credit_amount' => $asset->purchase_cost]);

            if ($proceeds > 0 && $request->proceeds_account_id) {
                JournalLine::create(['journal_entry_id' => $je->id, 'account_id' => $request->proceeds_account_id,
                    'description' => 'Disposal proceeds', 'debit_amount' => $proceeds, 'credit_amount' => 0]);
            }

            $asset->update([
                'status'       => 'disposed',
                'disposal_date'=> $request->disposal_date,
            ]);
        });

        return back()->with('success', 'Asset disposed and journal entry posted.');
    }

    public function destroy(string $slug, FixedAsset $asset)
    {
        $tenant = app('tenant');
        abort_if($asset->tenant_id !== $tenant->id, 403);
        $asset->update(['is_deleted' => true, 'deleted_at' => now()]);
        return redirect()->route('tenant.accounting.assets.index', $slug)->with('success', 'Asset deleted.');
    }
}
