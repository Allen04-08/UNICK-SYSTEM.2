<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionBatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'product' => new ProductResource($this->whenLoaded('product') ? $this->product : $this->product),
            'quantity_planned' => $this->quantity_planned,
            'quantity_completed' => $this->quantity_completed,
            'current_stage' => $this->currentStage?->name,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
        ];
    }
}
