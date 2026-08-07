<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view all invoices.
     * Only admins and superviseurs can view all invoices.
     */
    public function viewAny(User $user): bool
    {
        return $user->isApproved()
            && ($user->isAdmin() || $user->isSuperviseur() || $user->isAdv());
    }

    /**
     * Determine whether the user can view a specific invoice.
     * - Admins and superviseurs can view any invoice
     * - ADVs can only view invoices they created
     * - Regular users cannot view invoices (handle per role)
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if (!$user->isApproved()) {
            return false;
        }

        // Admins and superviseurs can view any invoice
        if ($user->isAdmin() || $user->isSuperviseur()) {
            return true;
        }

        // ADVs can only view invoices they created
        if ($user->isAdv()) {
            return $invoice->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create invoices.
     * Only admins, superviseurs, and ADVs can create invoices.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperviseur() || $user->isAdv();
    }

    /**
     * Determine whether the user can update an invoice.
     * - Admins can update any invoice
     * - Superviseurs can update any invoice (if unlocked)
     * - ADVs can only update their own invoices (if unlocked)
     */
    public function update(User $user, Invoice $invoice): bool
    {
        if (!$user->isApproved()) {
            return false;
        }

        // Locked invoices cannot be updated by anyone except admins
        if ($invoice->is_locked && !$user->isAdmin()) {
            return false;
        }

        // Admins can always update
        if ($user->isAdmin()) {
            return true;
        }

        // Superviseurs can update any unlocked invoice
        if ($user->isSuperviseur()) {
            return !$invoice->is_locked;
        }

        // ADVs can only update their own invoices
        if ($user->isAdv()) {
            return $invoice->created_by === $user->id && !$invoice->is_locked;
        }

        return false;
    }

    /**
     * Determine whether the user can delete an invoice.
     * - Only admins can delete invoices
     * - Deleted invoices should be soft-deleted (not permanently removed)
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin() && $user->isApproved();
    }

    /**
     * Determine whether the user can lock an invoice.
     * Locking prevents further modifications (except by admins).
     */
    public function lock(User $user, Invoice $invoice): bool
    {
        // Only admins and superviseurs can lock invoices
        if (!($user->isAdmin() || $user->isSuperviseur())) {
            return false;
        }

        // Cannot lock already locked invoices
        return !$invoice->is_locked;
    }

    /**
     * Determine whether the user can unlock an invoice.
     * Only admins can unlock invoices.
     */
    public function unlock(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin() && $invoice->is_locked;
    }

    /**
     * Determine whether the user can export invoice data.
     * Only admins and superviseurs can export.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperviseur();
    }
}
