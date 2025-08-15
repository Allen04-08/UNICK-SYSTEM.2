<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\ProductionLog;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    public function completeProduction(ProductionBatch $batch, int $quantityCompleted, ?int $stageId = null, ?string $note = null): ProductionBatch
    {
        return DB::transaction(function () use ($batch, $quantityCompleted, $stageId, $note) {
            $batch->quantity_completed = (int) $batch->quantity_completed + max(0, (int) $quantityCompleted);
            $batch->status = 'completed';
            $batch->save();

            ProductionLog::create([
                'production_batch_id' => $batch->id,
                'stage_id' => $stageId,
                'log_date' => now()->toDateString(),
                'quantity_completed' => $quantityCompleted,
                'note' => $note,
            ]);

            $product = $batch->product()->lockForUpdate()->firstOrFail();
            $product->stock_on_hand = (int) $product->stock_on_hand + (int) $quantityCompleted;
            $product->save();

            InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'inbound',
                'quantity_change' => $quantityCompleted,
                'stock_after' => $product->stock_on_hand,
                'reference_type' => ProductionBatch::class,
                'reference_id' => $batch->id,
                'note' => 'Production completed',
            ]);

            // If BOM exists, consume raw materials proportionally
            $bom = $product->boms()->with('items.component')->first();
            if ($bom) {
                foreach ($bom->items as $item) {
                    $required = (int) ceil(($item->quantity_per_unit * $quantityCompleted) * (1 + (float) $item->waste_factor));
                    $component = $item->component()->lockForUpdate()->first();
                    if ($component) {
                        $component->stock_on_hand = max(0, (int) $component->stock_on_hand - $required);
                        $component->save();
                        InventoryMovement::create([
                            'product_id' => $component->id,
                            'movement_type' => 'outbound',
                            'quantity_change' => -$required,
                            'stock_after' => $component->stock_on_hand,
                            'reference_type' => ProductionBatch::class,
                            'reference_id' => $batch->id,
                            'note' => 'Material consumption for production',
                        ]);
                    }
                }
            }

            return $batch->load('product','currentStage');
        });
    }
}