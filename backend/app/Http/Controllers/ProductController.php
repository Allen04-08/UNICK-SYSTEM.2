<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ForecastService;
use App\Services\MRPService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()->orderBy('name')->paginate(20);
        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);
        $product = Product::create($request->validated());
        activity()->performedOn($product)->causedBy(auth()->user())->withProperties($request->validated())->log('product_created');
        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $product->update($request->validated());
        activity()->performedOn($product)->causedBy(auth()->user())->withProperties($request->validated())->log('product_updated');
        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        activity()->performedOn($product)->causedBy(auth()->user())->log('product_deleted');
        return response()->noContent();
    }

    public function forecast(Product $product, ForecastService $forecastService, MRPService $mrpService)
    {
        $this->authorize('view', $product);
        $data = $forecastService->demandForecastForProduct($product);
        $stock = $mrpService->computeStockStatus($product, $data['daily_demand']);
        return response()->json(['forecast' => $data, 'stock' => $stock]);
    }
}
