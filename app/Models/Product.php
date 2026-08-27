<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'code',
        'category',
        'unit',
        'base_price',
        'cost_price',
        'stock_quantity',
        'reserved_quantity',
        'low_stock_threshold',
        'status',
        'image',
        'description',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->orderBy('created_at', 'desc');
    }

    public function customerPrices(): HasMany
    {
        return $this->hasMany(CustomerProductPrice::class);
    }

    public function getAvailableStockAttribute(): float
    {
        return max(0, (float)$this->stock_quantity - (float)$this->reserved_quantity);
    }

    public function isLowStock(): bool
    {
        return $this->available_stock <= (float)$this->low_stock_threshold;
    }
}
