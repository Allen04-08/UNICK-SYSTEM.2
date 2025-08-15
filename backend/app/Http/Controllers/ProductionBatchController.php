<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductionBatchRequest;
use App\Http\Resources\ProductionBatchResource;
use App\Models\ProductionBatch;
use App\Services\ProductionService;
use Illuminate\Http\Request;

class ProductionBatchController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ProductionBatch::class);
        $batches = ProductionBatch::with('product','currentStage')->latest()->paginate(20);
        return ProductionBatchResource::collection($batches);
    }

    public function store(ProductionBatchRequest $request)
    {
        $this->authorize('create', ProductionBatch::class);
        $data = $request->validated();
        $data['batch_number'] = $data['batch_number'] ?? 'BAT-'.now()->format('YmdHis');
        $batch = ProductionBatch::create($data);
        return new ProductionBatchResource($batch->load('product','currentStage'));
    }

    public function show(ProductionBatch $batch)
    {
        $this->authorize('view', $batch);
        return new ProductionBatchResource($batch->load('product','currentStage'));
    }

    public function update(ProductionBatchRequest $request, ProductionBatch $batch)
    {
        $this->authorize('update', $batch);
        $batch->update($request->validated());
        return new ProductionBatchResource($batch->load('product','currentStage'));
    }

    public function complete(Request $request, ProductionBatch $batch, ProductionService $productionService)
    {
        $this->authorize('update', $batch);
        $qty = (int) $request->input('quantity_completed', 0);
        $stageId = $request->input('stage_id');
        $note = $request->input('note');
        $batch = $productionService->completeProduction($batch, $qty, $stageId, $note);
        return new ProductionBatchResource($batch);
    }

    public function destroy(ProductionBatch $batch)
    {
        $this->authorize('delete', $batch);
        $batch->delete();
        return response()->noContent();
    }
}
