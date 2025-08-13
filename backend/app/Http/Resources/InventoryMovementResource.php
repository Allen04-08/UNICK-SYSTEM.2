<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product->id ?? null,
                'sku' => $this->product->sku ?? null,
                'name' => $this->product->name ?? null,
            ],
            'movement_type' => $this->movement_type,
            'quantity_change' => $this->quantity_change,
            'stock_after' => $this->stock_after,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
