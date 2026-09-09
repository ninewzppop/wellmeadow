<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Allow only users having one of the given roles.
     * Usage: Route::middleware(['auth', 'role:charge_nurse,doctor'])
     * Fail-closed: guests and unknown roles are redirected to the 403 page.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasRole($roles)) {
            return redirect()->route('forbidden');
        }

        return $next($request);
    }
}
