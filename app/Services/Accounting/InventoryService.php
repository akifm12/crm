<?php

// app/Services/Accounting/InventoryService.php

namespace App\Services\Accounting;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryBalance;
use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Increase stock (purchase/exchange-in/adjustment-in), recomputing the weighted-average
     * cost. Row-locked to prevent lost updates when two postings for the same item race.
     *
     * $pieces is an independently-tracked discrete count (e.g. "12 TT bars"), not derived
     * from quantity_grams — pass null for items that don't track denominated pieces (raw/
     * scrap), in which case the balance's pieces column is left untouched.
     */
    public function receiveStock(InventoryItem $item, float $qtyGrams, float $unitCost, array $source = [], ?int $pieces = null): InventoryStockMovement
    {
        $this->ensureBalance($item);

        return DB::transaction(function () use ($item, $qtyGrams, $unitCost, $source, $pieces) {
            $balance = InventoryBalance::where('inventory_item_id', $item->id)->lockForUpdate()->first();

            $oldQty = (float) $balance->quantity_grams;
            $oldAvg = (float) $balance->weighted_avg_cost;
            $newQty = round($oldQty + $qtyGrams, 3);
            $newAvg = $newQty > 0 ? (($oldQty * $oldAvg) + ($qtyGrams * $unitCost)) / $newQty : 0;

            $update = [
                'quantity_grams' => $newQty,
                'weighted_avg_cost' => round($newAvg, 4),
                'total_value' => round($newQty * $newAvg, 2),
                'updated_at' => now(),
            ];
            if ($pieces !== null) {
                $update['pieces'] = (int) ($balance->pieces ?? 0) + $pieces;
            }

            $balance->update($update);

            return InventoryStockMovement::create([
                'tenant_id' => $item->tenant_id,
                'inventory_item_id' => $item->id,
                'movement_type' => $source['movement_type'] ?? 'purchase_in',
                'quantity_grams' => $qtyGrams,
                'pieces' => $pieces,
                'unit_cost' => $unitCost,
                'total_cost' => round($qtyGrams * $unitCost, 2),
                'source_type' => $source['source_type'] ?? null,
                'source_id' => $source['source_id'] ?? null,
                'moved_at' => $source['moved_at'] ?? now(),
                'notes' => $source['notes'] ?? null,
            ]);
        });
    }

    /**
     * Decrease stock (sale/exchange-out/adjustment-out) at the current weighted-average
     * cost — the returned movement's total_cost is the COGS figure for the caller to post.
     * Hard-blocks on insufficient stock by default; protects the balance sheet from going
     * negative on inventory. Same nullable $pieces semantics as receiveStock().
     *
     * @throws InsufficientStockException
     */
    public function issueStock(InventoryItem $item, float $qtyGrams, array $source = [], ?int $pieces = null): InventoryStockMovement
    {
        $this->ensureBalance($item);

        return DB::transaction(function () use ($item, $qtyGrams, $source, $pieces) {
            $balance = InventoryBalance::where('inventory_item_id', $item->id)->lockForUpdate()->first();

            $available = (float) $balance->quantity_grams;

            if ($qtyGrams > $available + 0.001) {
                throw InsufficientStockException::forItem($item, $qtyGrams, $available);
            }

            $availablePieces = (int) ($balance->pieces ?? 0);
            if ($pieces !== null && $pieces > $availablePieces) {
                throw InsufficientStockException::forItemPieces($item, $pieces, $availablePieces);
            }

            $unitCost = (float) $balance->weighted_avg_cost;
            $newQty = max(0, round($available - $qtyGrams, 3));

            $update = [
                'quantity_grams' => $newQty,
                'total_value' => round($newQty * $unitCost, 2),
                'updated_at' => now(),
            ];
            if ($pieces !== null) {
                $update['pieces'] = $availablePieces - $pieces;
            }

            $balance->update($update);

            return InventoryStockMovement::create([
                'tenant_id' => $item->tenant_id,
                'inventory_item_id' => $item->id,
                'movement_type' => $source['movement_type'] ?? 'sale_out',
                'quantity_grams' => $qtyGrams,
                'pieces' => $pieces,
                'unit_cost' => $unitCost,
                'total_cost' => round($qtyGrams * $unitCost, 2),
                'source_type' => $source['source_type'] ?? null,
                'source_id' => $source['source_id'] ?? null,
                'moved_at' => $source['moved_at'] ?? now(),
                'notes' => $source['notes'] ?? null,
            ]);
        });
    }

    /**
     * Algebraically undo a specific movement's effect on the balance — used when voiding
     * an invoice. This is the exact inverse of the receive/issue math for that one movement,
     * not a full cost-layer rewind: if other transactions posted after this movement moved
     * the weighted-average, the reversal does not retroactively correct those — an accepted
     * limitation of weighted-average costing, not specific to this implementation.
     */
    public function reverseMovement(InventoryStockMovement $movement): void
    {
        DB::transaction(function () use ($movement) {
            $balance = InventoryBalance::where('inventory_item_id', $movement->inventory_item_id)->lockForUpdate()->first();

            $isIncoming = in_array($movement->movement_type, ['purchase_in', 'exchange_in', 'adjustment_in']);
            $qty = (float) $movement->quantity_grams;
            $cost = (float) $movement->unit_cost;
            $pieces = $movement->pieces !== null ? (int) $movement->pieces : null;

            $oldQty = (float) $balance->quantity_grams;
            $oldTotalValue = (float) $balance->total_value;
            $oldPieces = (int) ($balance->pieces ?? 0);

            if ($isIncoming) {
                $newQty = max(0, round($oldQty - $qty, 3));
                $newTotalValue = max(0, round($oldTotalValue - ($qty * $cost), 2));
                $newPieces = $pieces !== null ? max(0, $oldPieces - $pieces) : null;
            } else {
                $newQty = round($oldQty + $qty, 3);
                $newTotalValue = round($oldTotalValue + ($qty * $cost), 2);
                $newPieces = $pieces !== null ? $oldPieces + $pieces : null;
            }

            $newAvg = $newQty > 0 ? round($newTotalValue / $newQty, 4) : 0;

            $update = [
                'quantity_grams' => $newQty,
                'weighted_avg_cost' => $newAvg,
                'total_value' => $newQty > 0 ? round($newQty * $newAvg, 2) : 0,
                'updated_at' => now(),
            ];
            if ($newPieces !== null) {
                $update['pieces'] = $newPieces;
            }

            $balance->update($update);

            InventoryStockMovement::create([
                'tenant_id' => $movement->tenant_id,
                'inventory_item_id' => $movement->inventory_item_id,
                'movement_type' => $isIncoming ? 'adjustment_out' : 'adjustment_in',
                'quantity_grams' => $qty,
                'pieces' => $pieces,
                'unit_cost' => $cost,
                'total_cost' => round($qty * $cost, 2),
                'source_type' => 'void',
                'source_id' => $movement->id,
                'moved_at' => now(),
                'notes' => 'Reversal of movement #'.$movement->id,
            ]);
        });
    }

    private function ensureBalance(InventoryItem $item): void
    {
        if (InventoryBalance::where('inventory_item_id', $item->id)->exists()) {
            return;
        }

        try {
            InventoryBalance::create([
                'tenant_id' => $item->tenant_id,
                'inventory_item_id' => $item->id,
                'quantity_grams' => 0,
                'pieces' => 0,
                'weighted_avg_cost' => 0,
                'total_value' => 0,
                'updated_at' => now(),
            ]);
        } catch (QueryException $e) {
            // Concurrent first-movement race lost to another request — the unique
            // constraint on inventory_item_id means a balance row now exists either way.
        }
    }
}
