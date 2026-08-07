<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ExecutiveDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        Gate::authorize('view-dashboard');

        return $next($request);
    }
}
