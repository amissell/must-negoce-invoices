<?php

namespace App\Policies;

use App\Models\Vendeur;
use App\Models\User;

class VendorPolicy
{
    /**
     * Determine if the user can view all vendors
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->isApproved();
    }

    /**
     * Determine if the user can view this vendor
     */
    public function view(User $user, Vendeur $vendor): bool
    {
        // Admin can view all vendors
        if ($user->isAdmin()) {
            return $user->isApproved();
        }

        // Supervisors can view all vendors
        if ($user->isSuperviseur()) {
            return $user->isApproved();
        }

        // Users cannot view other vendors' data
        return false;
    }

    /**
     * Determine if the user can create vendors
     */
    public function create(User $user): bool
    {
        return $user->isApproved() && $user->isAdmin();
    }

    /**
     * Determine if the user can update this vendor
     */
    public function update(User $user, Vendeur $vendor): bool
    {
        // Only admins and supervisors can update vendors
        if (!$user->isApproved()) {
            return false;
        }

        return $user->isAdmin() || $user->isSuperviseur();
    }

    /**
     * Determine if the user can delete this vendor
     */
    public function delete(User $user, Vendeur $vendor): bool
    {
        // Only admin can delete vendors
        return $user->isAdmin() && $user->isApproved();
    }

    /**
     * Determine if the user can restore this vendor
     */
    public function restore(User $user, Vendeur $vendor): bool
    {
        return $user->isAdmin() && $user->isApproved();
    }

    /**
     * Determine if the user can permanently delete this vendor
     */
    public function forceDelete(User $user, Vendeur $vendor): bool
    {
        return $user->isAdmin() && $user->isApproved();
    }
}
