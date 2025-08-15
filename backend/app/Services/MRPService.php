<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MRPService
{
    public function computeStockStatus(Product $product, int $dailyDemand): array
    {
        $available = max(0, (int) $product->stock_on_hand - (int) $product->stock_allocated);
        $reorderPoint = (int) max(0, round($dailyDemand * max(0, (int) $product->lead_time_days) + (int) $product->safety_stock));
        $needReorder = $available <= $reorderPoint;
        $recommendedOrderQty = $needReorder ? max(0, $reorderPoint + $dailyDemand * 7 - $available) : 0; // one week cover

        return compact('available','reorderPoint','needReorder','recommendedOrderQty');
    }

    public function planProductionForShortages(Order $order): array
    {
        $planned = [];
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product || $product->type !== 'finished') {
                continue;
            }
            $available = max(0, (int) $product->stock_on_hand - (int) $product->stock_allocated);
            $shortage = max(0, (int) $item->quantity - $available);
            if ($shortage > 0) {
                $batch = ProductionBatch::create([
                    'batch_number' => 'MRP-'.now()->format('YmdHis').'-'.$product->id,
                    'product_id' => $product->id,
                    'quantity_planned' => $shortage,
                    'status' => 'scheduled',
                    'start_date' => Carbon::now()->toDateString(),
                    'due_date' => Carbon::now()->addDays(max(1,(int) $product->lead_time_days))->toDateString(),
                ]);
                $planned[] = $batch;
            }
        }
        return $planned;
    }

    public function computeMaterialRequirementsForProduct(Product $finishedProduct, int $quantity): array
    {
        $bom = Bom::where('product_id', $finishedProduct->id)->first();
        if (!$bom) {
            return [];
        }
        $requirements = [];
        foreach ($bom->items as $bi) {
            $qty = ($bi->quantity_per_unit * $quantity) * (1 + (float) $bi->waste_factor);
            $requirements[] = [
                'component_product_id' => $bi->component_product_id,
                'required_quantity' => (int) ceil($qty),
            ];
        }
        return $requirements;
    }
}