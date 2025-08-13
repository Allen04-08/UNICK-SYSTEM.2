<?php

namespace App\Policies;

use App\Models\UserFeedback;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin','staff','customer'], true);
    }

    public function view(User $user, UserFeedback $feedback): bool
    {
        return $user->role === 'admin' || $user->role === 'staff' || $feedback->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin','staff','customer'], true);
    }

    public function update(User $user, UserFeedback $feedback): bool
    {
        return $feedback->user_id === $user->id || in_array($user->role, ['admin','staff'], true);
    }

    public function delete(User $user, UserFeedback $feedback): bool
    {
        return $user->role === 'admin';
    }
}
