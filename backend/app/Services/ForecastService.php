<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    public function demandForecastForProduct(Product $product, int $daysLookback = 90): array
    {
        // Aggregate ordered quantities per day for finished goods
        $startDate = Carbon::now()->subDays($daysLookback)->startOfDay();
        $rows = OrderItem::query()
            ->select(DB::raw('DATE(orders.created_at) as day'), DB::raw('SUM(order_items.quantity) as qty'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $product->id)
            ->whereNotIn('orders.status', ['cancelled'])
            ->where('orders.created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->orderBy('day')
            ->get();

        $daily = collect();
        for ($i = 0; $i <= $daysLookback; $i++) {
            $day = Carbon::now()->subDays($daysLookback - $i)->toDateString();
            $daily[$day] = 0;
        }
        foreach ($rows as $r) {
            $daily[$r->day] = (int) $r->qty;
        }

        $window7 = $this->simpleMovingAverage($daily->values(), 7);
        $window30 = $this->simpleMovingAverage($daily->values(), 30);

        $avg7 = (int) round(collect($window7)->last() ?? 0);
        $avg30 = (int) round(collect($window30)->last() ?? 0);

        $leadDays = max(0, (int) $product->lead_time_days);
        $safety = max(0, (int) $product->safety_stock);
        $dailyDemand = max($avg7, $avg30);
        $reorderPoint = (int) max(0, round($dailyDemand * $leadDays + $safety));

        return [
            'daily_series' => $daily,
            'sma7_series' => $window7,
            'sma30_series' => $window30,
            'avg7' => $avg7,
            'avg30' => $avg30,
            'daily_demand' => $dailyDemand,
            'reorder_point' => $reorderPoint,
        ];
    }

    /**
     * @param Collection|array<int,int|float> $series
     * @return array<int,float>
     */
    private function simpleMovingAverage($series, int $window): array
    {
        $values = collect($series)->values()->all();
        $n = count($values);
        $res = [];
        $sum = 0;
        for ($i = 0; $i < $n; $i++) {
            $sum += $values[$i];
            if ($i >= $window) {
                $sum -= $values[$i - $window];
            }
            $res[] = $i + 1 >= $window ? $sum / $window : 0.0;
        }
        return $res;
    }
}