<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku','name','description','type','reorder_point','safety_stock','lead_time_days','unit_price','stock_on_hand','stock_allocated'
    ];

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productionBatches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function boms(): HasMany
    {
        return $this->hasMany(Bom::class);
    }

    public function getStockAvailableAttribute(): int
    {
        return max(0, (int) $this->stock_on_hand - (int) $this->stock_allocated);
    }
}
