<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with('items.product','user')->latest();
        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }
        return OrderResource::collection($query->paginate(20));
    }

    public function store(OrderRequest $request)
    {
        $user = $request->user();

        $payload = $request->validated();
        $items = $payload['items'];

        $order = DB::transaction(function () use ($user, $payload, $items) {
            $order = new Order();
            $order->fill([
                'user_id' => $user->id,
                'order_number' => 'ORD-'.now()->format('YmdHis').'-'.random_int(1000,9999),
                'status' => 'pending',
                'shipping_address' => $payload['shipping_address'] ?? null,
                'billing_address' => $payload['billing_address'] ?? null,
            ]);
            $order->subtotal = 0;
            $order->tax = 0;
            $order->total = 0;
            $order->save();

            $subtotal = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->unit_price;
                $lineTotal = $unitPrice * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;

                $product->increment('stock_allocated', $quantity);
            }

            $order->subtotal = $subtotal;
            $order->tax = round($subtotal * 0.12, 2);
            $order->total = round($order->subtotal + $order->tax, 2);
            $order->save();

            // After items saved, trigger MRP planning for shortages
            app(\App\Services\MRPService::class)->planProductionForShortages($order);

            return $order->load('items.product','user');
        });

        return new OrderResource($order);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        return new OrderResource($order->load('items.product','user'));
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        $order->delete();
        return response()->noContent();
    }
}
