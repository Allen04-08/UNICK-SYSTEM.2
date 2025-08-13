<?php

namespace App\Providers;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\ProductionBatch;
use App\Models\Product;
use App\Models\UserFeedback;
use App\Policies\FeedbackPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductionBatchPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        ProductionBatch::class => ProductionBatchPolicy::class,
        InventoryMovement::class => InventoryPolicy::class,
        UserFeedback::class => FeedbackPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}