<?php

namespace App\Http\Middleware;

use App\Models\Sim;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSimAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof Sim) {
            return response()->json([
                'message' => 'Unauthorized. SIM authentication is required.'
            ], 403);
        }

        return $next($request);
    }
}

