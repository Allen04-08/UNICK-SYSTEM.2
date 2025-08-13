<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'reorder_point' => $this->reorder_point,
            'safety_stock' => $this->safety_stock,
            'lead_time_days' => $this->lead_time_days,
            'unit_price' => $this->unit_price,
            'stock_on_hand' => $this->stock_on_hand,
            'stock_allocated' => $this->stock_allocated,
            'stock_available' => $this->stock_available,
        ];
    }
}
