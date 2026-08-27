<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record stock movement (purchase, sale, wastage, damage, adjustment)
     */
    public function recordTransaction(int $productId, string $type, float $quantity, ?string $notes = null, ?string $refType = null, ?int $refId = null, ?int $userId = null): InventoryTransaction
    {
        return DB::transaction(function () use ($productId, $type, $quantity, $notes, $refType, $refId, $userId) {
            $product = Product::findOrFail($productId);

            // Calculate quantity delta
            // 'purchase', 'adjustment_in', 'return' -> positive delta
            // 'sale', 'wastage', 'damage', 'adjustment_out' -> negative delta
            $delta = in_array($type, ['purchase', 'adjustment_in', 'return', 'stock_in']) ? abs($quantity) : -abs($quantity);

            $newStock = max(0, (float)$product->stock_quantity + $delta);
            $product->update(['stock_quantity' => $newStock]);

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $delta,
                'balance_after' => $newStock,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes,
                'created_by' => $userId,
            ]);
        });
    }
}
