<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class CheckApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): mixed
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if ($user && !$user->isApproved() && !$user->isAdmin()) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'User account is pending approval.'], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN, 'User account is pending approval.');
    }

    return $next($request);
}
}
