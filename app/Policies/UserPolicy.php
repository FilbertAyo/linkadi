<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view themselves, admins can view anyone
        return $user->id === $model->id || $user->can('view users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Prevent users from modifying their own admin status
        if ($user->id === $model->id && $model->hasRole('admin')) {
            return false; // Admins cannot remove their own admin role
        }
        
        return $user->can('edit users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Prevent self-deletion and deletion of other admins
        if ($user->id === $model->id) {
            return false; // Cannot delete yourself
        }
        
        if ($model->hasRole('admin') && !$user->hasRole('admin')) {
            return false; // Only admins can delete admins
        }
        
        return $user->can('delete users');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('edit users');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Prevent self-deletion and deletion of other admins
        if ($user->id === $model->id) {
            return false;
        }
        
        if ($model->hasRole('admin') && !$user->hasRole('admin')) {
            return false;
        }
        
        return $user->can('delete users');
    }
}
