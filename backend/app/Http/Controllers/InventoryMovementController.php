<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', InventoryMovement::class);
        $movements = InventoryMovement::with('product')->latest()->paginate(20);
        return InventoryMovementResource::collection($movements);
    }

    public function store(InventoryMovementRequest $request)
    {
        $this->authorize('create', InventoryMovement::class);
        $data = $request->validated();

        $movement = DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            $newStock = $product->stock_on_hand + (int) $data['quantity_change'];
            if ($data['movement_type'] === 'outbound') {
                $newStock = $product->stock_on_hand - abs((int) $data['quantity_change']);
            } elseif ($data['movement_type'] === 'inbound') {
                $newStock = $product->stock_on_hand + abs((int) $data['quantity_change']);
            }

            $product->update(['stock_on_hand' => max(0, $newStock)]);

            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => $data['movement_type'],
                'quantity_change' => (int) $data['quantity_change'],
                'stock_after' => $product->stock_on_hand,
                'note' => $data['note'] ?? null,
            ]);

            return $movement->load('product');
        });

        return new InventoryMovementResource($movement);
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryMovement $inventoryMovement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryMovement $inventoryMovement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryMovement $inventoryMovement)
    {
        //
    }
}
