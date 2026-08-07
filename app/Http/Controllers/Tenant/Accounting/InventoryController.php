<?php

// app/Http/Controllers/Tenant/Accounting/InventoryController.php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Services\Accounting\InventoryService;
use App\Services\Accounting\LedgerService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        $items = InventoryItem::where('tenant_id', $tenant->id)
            ->with('balance')
            ->orderBy('metal_type')
            ->orderByRaw('nominal_weight_grams IS NULL')
            ->orderBy('nominal_weight_grams')
            ->orderBy('name')
            ->get();

        return view('tenant.accounting.inventory.index', compact('tenant', 'items'));
    }

    public function create()
    {
        $tenant = app('tenant');

        return view('tenant.accounting.inventory.create', compact('tenant'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'metal_type' => 'required|in:'.implode(',', InventoryItem::METAL_TYPES),
            'purity' => 'nullable|numeric|min:0|max:999.999',
            'nominal_weight_grams' => 'nullable|numeric|min:0',
            'form' => 'nullable|in:bar,coin,jewellery,raw,scrap',
        ]);

        InventoryItem::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'sku' => $request->sku,
            'metal_type' => $request->metal_type,
            'purity' => $request->purity,
            'nominal_weight_grams' => $request->nominal_weight_grams,
            'form' => $request->form,
            'is_active' => true,
        ]);

        return redirect()->route('tenant.accounting.inventory.index', $tenant->slug)
            ->with('success', 'Inventory item created.');
    }

    public function show(string $slug, InventoryItem $item)
    {
        $tenant = app('tenant');
        abort_if($item->tenant_id !== $tenant->id, 404);

        $item->load('balance');
        $movements = $item->movements()->orderByDesc('moved_at')->orderByDesc('id')->paginate(25);

        return view('tenant.accounting.inventory.show', compact('tenant', 'item', 'movements'));
    }

    public function adjust(Request $request, string $slug, InventoryItem $item, InventoryService $inventory, LedgerService $ledger)
    {
        $tenant = app('tenant');
        abort_if($item->tenant_id !== $tenant->id, 404);

        $request->validate([
            'direction' => 'required|in:in,out',
            'quantity_grams' => 'required|numeric|min:0.001',
            'pieces' => 'nullable|integer|min:1',
            'unit_cost' => 'required_if:direction,in|nullable|numeric|min:0',
            'reason_type' => 'required|in:correction,capital_contribution',
            'reason' => 'required|string|max:255',
        ]);

        $qty = (float) $request->quantity_grams;
        $pieces = $request->filled('pieces') ? (int) $request->pieces : null;

        // 'correction' = stock-count discrepancy, hits Inventory Adjustments (income-type,
        // affects P&L). 'capital_contribution' = an owner/shareholder funding or drawing
        // from the business in physical gold, hits Owner's Equity directly (balance sheet
        // only, never touches the income statement).
        $contraSubtype = $request->reason_type === 'capital_contribution' ? 'owners_equity' : 'inventory_adjustment';
        $inventorySubtype = 'inventory_'.$item->metal_type;

        try {
            if ($request->direction === 'in') {
                $movement = $inventory->receiveStock($item, $qty, (float) $request->unit_cost, [
                    'movement_type' => 'adjustment_in',
                    'notes' => $request->reason,
                ], $pieces);
                $inventoryAccount = $ledger->accountFor($tenant, $inventorySubtype);
                $contraAccount = $ledger->accountFor($tenant, $contraSubtype);

                $entry = $ledger->post($tenant, [
                    'entry_date' => now()->toDateString(),
                    'reference' => 'Stock adjustment — '.$item->name,
                    'source_type' => 'stock_adjustment',
                    'source_id' => $movement->id,
                    'memo' => $request->reason,
                    'created_by' => auth()->id(),
                ], [
                    ['chart_of_account_id' => $inventoryAccount->id, 'debit' => $movement->total_cost, 'credit' => 0, 'description' => $request->reason],
                    ['chart_of_account_id' => $contraAccount->id, 'debit' => 0, 'credit' => $movement->total_cost, 'description' => $request->reason],
                ]);
            } else {
                $movement = $inventory->issueStock($item, $qty, [
                    'movement_type' => 'adjustment_out',
                    'notes' => $request->reason,
                ], $pieces);
                $inventoryAccount = $ledger->accountFor($tenant, $inventorySubtype);
                $contraAccount = $ledger->accountFor($tenant, $contraSubtype);

                $entry = $ledger->post($tenant, [
                    'entry_date' => now()->toDateString(),
                    'reference' => 'Stock adjustment — '.$item->name,
                    'source_type' => 'stock_adjustment',
                    'source_id' => $movement->id,
                    'memo' => $request->reason,
                    'created_by' => auth()->id(),
                ], [
                    ['chart_of_account_id' => $contraAccount->id, 'debit' => $movement->total_cost, 'credit' => 0, 'description' => $request->reason],
                    ['chart_of_account_id' => $inventoryAccount->id, 'debit' => 0, 'credit' => $movement->total_cost, 'description' => $request->reason],
                ]);
            }

            $movement->update(['journal_entry_id' => $entry->id]);
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock adjustment recorded.');
    }
}
