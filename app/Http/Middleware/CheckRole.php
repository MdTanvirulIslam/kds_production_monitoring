<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  - Comma-separated roles (e.g., 'admin', 'admin,supervisor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        // Get user's role
        $userRole = $request->user()->role;

        // Check if user's role is in the allowed roles
        // Roles can be passed as 'admin,supervisor' or as separate parameters
        $allowedRoles = [];
        foreach ($roles as $role) {
            // Split by comma in case multiple roles are passed as one string
            $splitRoles = explode(',', $role);
            $allowedRoles = array_merge($allowedRoles, $splitRoles);
        }

        // Clean up and check
        $allowedRoles = array_map('trim', $allowedRoles);

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // User doesn't have required role
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Forbidden - Insufficient permissions'], 403);
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
