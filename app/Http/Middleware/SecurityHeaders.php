<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Legacy XSS filter (IE/old browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Limit referrer info sent to third parties
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict browser feature access
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // Force HTTPS for 1 year (only effective in production over HTTPS)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remove headers that expose server technology
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
