<?php

namespace App\Policies;

use App\Models\ScheduledReport;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine if the user can view all reports
     */
    public function viewAny(User $user): bool
    {
        return $user->isApproved() && in_array($user->role, ['admin', 'superviseur']);
    }

    /**
     * Determine if the user can view this report
     */
    public function view(User $user, ScheduledReport $report): bool
    {
        // Must be approved
        if (!$user->isApproved()) {
            return false;
        }

        // Admin and supervisors can view all reports
        if ($user->isAdmin() || $user->isSuperviseur()) {
            return true;
        }

        // Other roles cannot view reports
        return false;
    }

    /**
     * Determine if the user can create reports
     */
    public function create(User $user): bool
    {
        return $user->isApproved() && in_array($user->role, ['admin', 'superviseur']);
    }

    /**
     * Determine if the user can update this report
     */
    public function update(User $user, ScheduledReport $report): bool
    {
        if (!$user->isApproved()) {
            return false;
        }

        return $user->isAdmin() || $user->isSuperviseur();
    }

    /**
     * Determine if the user can delete this report
     */
    public function delete(User $user, ScheduledReport $report): bool
    {
        return $user->isAdmin() && $user->isApproved();
    }

    /**
     * Determine if the user can run/execute this report
     */
    public function execute(User $user, ScheduledReport $report): bool
    {
        return $user->isApproved() && in_array($user->role, ['admin', 'superviseur']);
    }

    /**
     * Determine if the user can export this report
     */
    public function export(User $user, ScheduledReport $report): bool
    {
        return $user->isApproved() && in_array($user->role, ['admin', 'superviseur']);
    }
}
