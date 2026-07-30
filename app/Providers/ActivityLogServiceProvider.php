<?php

namespace App\Providers;

use App\Models\Activity;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Activity::saving(function (Activity $activity) {
            if (app()->runningInConsole() && !app()->runningUnitTests()) {
                return;
            }

            if (request()) {
                $activity->ip_address = $activity->ip_address ?? request()->ip();
                $activity->user_agent = $activity->user_agent ?? substr((string) request()->userAgent(), 0, 1000);
            }
        });
    }
}
