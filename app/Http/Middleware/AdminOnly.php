<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AdminOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
            }
            return $next($request);
    }
}