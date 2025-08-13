<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles = ''): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $allowedRoles = array_filter(explode('|', $roles));
        if (!empty($allowedRoles) && !in_array($user->role ?? '', $allowedRoles, true)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
