<?php

namespace App\Policies;

use App\Models\ProductionBatch;
use App\Models\User;

class ProductionBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin','staff'], true);
    }

    public function view(User $user, ProductionBatch $productionBatch): bool
    {
        return in_array($user->role, ['admin','staff'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin','staff'], true);
    }

    public function update(User $user, ProductionBatch $productionBatch): bool
    {
        return in_array($user->role, ['admin','staff'], true);
    }

    public function delete(User $user, ProductionBatch $productionBatch): bool
    {
        return $user->role === 'admin';
    }
}
