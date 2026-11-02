<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $tenant   = app('tenant');
        $category = $request->get('category');

        $query = InventoryItem::where('tenant_id', $tenant->id)->orderBy('category')->orderBy('name');
        if ($category) $query->where('category', $category);

        $items = $query->get();

        $totalItems    = $items->count();
        $totalValue    = $items->sum(fn($i) => $i->stock_qty * $i->current_price);
        $lowStockItems = $items->filter(fn($i) => $i->stock_qty <= 0)->count();

        return view('tenant.accounting.inventory.index', compact('tenant', 'items', 'totalItems', 'totalValue', 'lowStockItems', 'category'));
    }

    public function create(Request $request, string $slug)
    {
        $tenant   = app('tenant');
        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('code')->get();
        return view('tenant.accounting.inventory.create', compact('tenant', 'accounts'));
    }

    public function store(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate(['name' => 'required|string']);

        InventoryItem::create([
            'tenant_id'             => $tenant->id,
            'code'                  => $request->code,
            'name'                  => $request->name,
            'name_arabic'           => $request->name_arabic,
            'category'              => $request->category ?? 'other',
            'unit'                  => $request->unit ?? 'pcs',
            'gold_purity'           => $request->gold_purity ?: null,
            'weight_per_unit_grams' => $request->weight_per_unit_grams ?: null,
            'current_price'         => floatval($request->current_price ?? 0),
            'cost_price'            => floatval($request->cost_price ?? 0),
            'stock_qty'             => floatval($request->opening_stock ?? 0),
            'vat_rate'              => floatval($request->vat_rate ?? 0),
            'sales_account_id'      => $request->sales_account_id ?: null,
            'inventory_account_id'  => $request->inventory_account_id ?: null,
            'cogs_account_id'       => $request->cogs_account_id ?: null,
            'is_active'             => true,
            'notes'                 => $request->notes,
            'created_by'            => auth()->id(),
        ]);

        return redirect()->route('tenant.accounting.inventory.index', $tenant->slug)
            ->with('success', 'Item created.');
    }

    public function show(string $slug, InventoryItem $item)
    {
        $tenant = app('tenant');
        abort_if($item->tenant_id !== $tenant->id, 404);

        $movements = DB::table('inventory_movements')
            ->where('inventory_item_id', $item->id)
            ->orderByDesc('movement_date')->orderByDesc('id')
            ->limit(50)->get();

        return view('tenant.accounting.inventory.show', compact('tenant', 'item', 'movements'));
    }

    public function adjust(Request $request, string $slug, InventoryItem $item)
    {
        $tenant = app('tenant');
        abort_if($item->tenant_id !== $tenant->id, 404);
        $request->validate(['movement_type' => 'required|string', 'quantity' => 'required|numeric|min:0.0001', 'movement_date' => 'required|date']);

        $qty  = floatval($request->quantity);
        $isIn = in_array($request->movement_type, ['purchase_in', 'adjustment_in', 'opening']);
        $signed = $isIn ? $qty : -$qty;

        DB::transaction(function () use ($tenant, $item, $request, $signed, $qty) {
            DB::table('inventory_movements')->insert([
                'tenant_id'          => $tenant->id,
                'inventory_item_id'  => $item->id,
                'movement_type'      => $request->movement_type,
                'movement_date'      => $request->movement_date,
                'quantity'           => $signed,
                'unit_price'         => floatval($request->unit_price ?? $item->current_price),
                'reference'          => $request->reference,
                'notes'              => $request->notes,
                'created_by'         => auth()->id(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $item->increment('stock_qty', $signed);

            if ($request->update_price && $request->unit_price) {
                $item->update(['current_price' => floatval($request->unit_price)]);
            }
        });

        return redirect()->route('tenant.accounting.inventory.show', [$tenant->slug, $item])
            ->with('success', 'Stock adjusted.');
    }

    public function optionsIndex(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $items  = InventoryItem::where('tenant_id', $tenant->id)->orderBy('category')->orderBy('name')->get();
        return response()->json($items->map(fn($i) => [
            'id'          => $i->id,
            'name'        => $i->name,
            'code'        => $i->code,
            'unit'        => $i->unit,
            'price'       => $i->current_price,
            'vat_rate'    => $i->vat_rate,
            'weight_grams'=> $i->weight_per_unit_grams,
            'category'    => $i->category,
        ]));
    }

    public function optionsStore(Request $request, string $slug)
    {
        // Quick-create a minimal item via AJAX from the invoice form
        $tenant = app('tenant');
        $request->validate(['name' => 'required|string']);

        $item = InventoryItem::create([
            'tenant_id'    => $tenant->id,
            'name'         => $request->name,
            'category'     => $request->category ?? 'other',
            'unit'         => $request->unit ?? 'pcs',
            'current_price'=> floatval($request->current_price ?? 0),
            'vat_rate'     => floatval($request->vat_rate ?? 0),
            'is_active'    => true,
            'created_by'   => auth()->id(),
        ]);

        return response()->json(['id' => $item->id, 'name' => $item->name, 'price' => $item->current_price, 'vat_rate' => $item->vat_rate]);
    }

    public function optionsDestroy(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $item   = InventoryItem::where('tenant_id', $tenant->id)->findOrFail($request->item_id);
        $item->update(['is_active' => false]);
        return response()->json(['ok' => true]);
    }
}
