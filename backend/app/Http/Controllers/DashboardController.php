<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductionBatch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function metrics()
    {
        $ordersToday = Order::whereDate('created_at', today())->count();
        $revenueMonth = Order::whereMonth('created_at', now()->month)->sum('total');
        $batchesInProgress = ProductionBatch::where('status', 'in_progress')->count();
        $lowStockCount = Product::whereRaw('stock_on_hand <= reorder_point + safety_stock')->count();

        return response()->json([
            'orders_today' => $ordersToday,
            'revenue_month' => $revenueMonth,
            'batches_in_progress' => $batchesInProgress,
            'low_stock_count' => $lowStockCount,
        ]);
    }
}
