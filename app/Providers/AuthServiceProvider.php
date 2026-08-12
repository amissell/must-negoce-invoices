<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Vendeur;
use App\Models\ScheduledReport;
use App\Policies\InvoicePolicy;
use App\Policies\ClientPolicy;
use App\Policies\VendorPolicy;
use App\Policies\ReportPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        Client::class => ClientPolicy::class,
        Vendeur::class => VendorPolicy::class,
        ScheduledReport::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin-only', function ($user) {
            return $user->isAdmin() && $user->isApproved();
        });

        Gate::define('superviseur-only', function ($user) {
            return $user->isSuperviseur() && $user->isApproved();
        });

        Gate::define('approved-only', function ($user) {
            return $user->isApproved();
        });

        Gate::define('view-dashboard', function ($user) {
            return in_array($user->role, ['admin', 'superviseur']) && $user->isApproved();
        });

        Gate::define('manage-reports', function ($user) {
            return in_array($user->role, ['admin', 'superviseur']) && $user->isApproved();
        });

        Gate::define('view-analytics', function ($user) {
            return $user->isApproved();
        });
    }
}
