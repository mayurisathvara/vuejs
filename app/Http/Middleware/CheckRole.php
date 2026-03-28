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
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = auth()->user();
        $userRole = $user->role ?? null;
        if (!is_string($userRole) || $userRole === '') {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to access this resource.'
            ], 403);
        }
        
        // Handle comma-separated roles from middleware parameter
        $allowedRoles = [];
        foreach ($roles as $role) {
            // Split by comma if roles are passed as comma-separated string
            $allowedRoles = array_merge($allowedRoles, explode(',', $role));
        }
        
        // Trim whitespace from roles
        $allowedRoles = array_map('trim', $allowedRoles);
        
        if (!in_array($userRole, $allowedRoles, true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}
