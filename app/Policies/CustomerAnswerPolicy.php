<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CustomerAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerAnswerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_customer::answer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomerAnswer $customerAnswer): bool
    {
        return $user->can('view_customer::answer');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_customer::answer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomerAnswer $customerAnswer): bool
    {
        return $user->can('update_customer::answer');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomerAnswer $customerAnswer): bool
    {
        return $user->can('delete_customer::answer');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_customer::answer');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, CustomerAnswer $customerAnswer): bool
    {
        return $user->can('force_delete_customer::answer');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_customer::answer');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, CustomerAnswer $customerAnswer): bool
    {
        return $user->can('restore_customer::answer');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_customer::answer');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, CustomerAnswer $customerAnswer): bool
    {
        return $user->can('replicate_customer::answer');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_customer::answer');
    }
}
