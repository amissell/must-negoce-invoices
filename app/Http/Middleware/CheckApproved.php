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
        Auth::logout();
        return redirect('/login')->with('error', '⏳ Votre compte est en attente d\'approbation.');
    }

    return $next($request);
}
}
