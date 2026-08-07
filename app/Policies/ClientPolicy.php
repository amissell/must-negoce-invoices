<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isApproved() && ($user->isAdmin() || $user->isSuperviseur() || $user->isAdv());
    }

    /**
     * Determine if the user can view this client
     */
    public function view(User $user, Client $client): bool
    {
        // Admin can view all clients
        if ($user->isAdmin()) {
            return $user->isApproved();
        }

        // Supervisors can view all clients
        if ($user->isSuperviseur()) {
            return $user->isApproved();
        }

        // ADVs can only view clients they've interacted with
        // For now, allow ADVs to view all (can be restricted based on relationships)
        return $user->isApproved() && $user->isAdv();
    }

    /**
     * Determine if the user can create clients
     */
    public function create(User $user): bool
    {
        return $user->isApproved() && in_array($user->role, ['admin', 'superviseur']);
    }

    /**
     * Determine if the user can update this client
     */
    public function update(User $user, Client $client): bool
    {
        // Only admins and supervisors can update clients
        if (!$user->isApproved()) {
            return false;
        }

        return $user->isAdmin() || $user->isSuperviseur();
    }

    /**
     * Determine if the user can delete this client
     */
    public function delete(User $user, Client $client): bool
    {
        // Only admin can delete clients
        return $user->isAdmin() && $user->isApproved();
    }

    /**
     * Determine if the user can restore this client
     */
    public function restore(User $user, Client $client): bool
    {
        return $user->isAdmin() && $user->isApproved();
    }

    /**
     * Determine if the user can permanently delete this client
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return $user->isAdmin() && $user->isApproved();
    }
}
