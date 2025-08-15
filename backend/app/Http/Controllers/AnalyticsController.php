<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ForecastService;
use App\Services\MRPService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function productForecast(Product $product, ForecastService $forecastService, MRPService $mrpService)
    {
        $data = $forecastService->demandForecastForProduct($product);
        $stock = $mrpService->computeStockStatus($product, $data['daily_demand']);
        return response()->json([
            'product' => new ProductResource($product),
            'forecast' => $data,
            'stock' => $stock,
        ]);
    }

    public function topForecasts(Request $request, ForecastService $forecastService, MRPService $mrpService)
    {
        $limit = (int) ($request->query('limit', 10));
        $products = Product::where('type','finished')->orderBy('name')->limit($limit)->get();
        $rows = [];
        foreach ($products as $p) {
            $f = $forecastService->demandForecastForProduct($p);
            $s = $mrpService->computeStockStatus($p, $f['daily_demand']);
            $rows[] = [
                'product' => new ProductResource($p),
                'forecast' => $f,
                'stock' => $s,
            ];
        }
        return response()->json($rows);
    }
}