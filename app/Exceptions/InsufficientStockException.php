<?php

// app/Exceptions/InsufficientStockException.php

namespace App\Exceptions;

use App\Models\InventoryItem;
use Exception;

class InsufficientStockException extends Exception
{
    public static function forItem(InventoryItem $item, float $requested, float $available): self
    {
        return new self(sprintf(
            'Insufficient stock for "%s": requested %.3fg, only %.3fg available.',
            $item->name,
            $requested,
            $available
        ));
    }

    public static function forItemPieces(InventoryItem $item, int $requested, int $available): self
    {
        return new self(sprintf(
            'Insufficient stock for "%s": requested %d pcs, only %d pcs available.',
            $item->name,
            $requested,
            $available
        ));
    }
}
