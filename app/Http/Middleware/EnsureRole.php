<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Usage: ->middleware('role:superadmin,admin') or ->middleware('role:superadmin|admin|supervisor|staff')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Forbidden: Inactive or unauthenticated user.'], 403);
            }
            abort(403, 'Your account is inactive or not authenticated.');
        }

        // Parse comma or pipe-delimited roles
        $allowedRoles = [];
        foreach ($roles as $roleString) {
            foreach (preg_split('/[,|]/', $roleString) as $item) {
                $clean = trim($item);
                if ($clean !== '') {
                    $allowedRoles[] = $clean;
                }
            }
        }

        // 1. Check via Spatie hasAnyRole
        $hasAccess = false;
        try {
            if ($user->hasAnyRole($allowedRoles)) {
                $hasAccess = true;
            }
        } catch (\Throwable $e) {
            $hasAccess = false;
        }

        // 2. Fallback check on user->role column
        if (! $hasAccess && in_array($user->role, $allowedRoles, true)) {
            $hasAccess = true;
        }

        // 3. Organizer / Staff alias compatibility
        if (! $hasAccess) {
            if (in_array('staff', $allowedRoles, true) && in_array($user->role, ['staff', 'organizer'], true)) {
                $hasAccess = true;
            }
            if (in_array('organizer', $allowedRoles, true) && in_array($user->role, ['staff', 'organizer'], true)) {
                $hasAccess = true;
            }
        }

        if (! $hasAccess) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Forbidden: You do not have the required access role.'], 403);
            }
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }
}
