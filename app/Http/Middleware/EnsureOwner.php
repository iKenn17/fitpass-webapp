<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the owner portal to active accounts with the "owner" role.
 * Use it by class name, no alias registration needed:
 *   Route::middleware(['auth', \App\Http\Middleware\EnsureOwner::class])
 */
class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->role === 'owner' && $user->is_active, 403, 'Owner access only.');

        return $next($request);
    }
}
