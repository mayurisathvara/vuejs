<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role'         => \App\Http\Middleware\CheckRole::class,
            'sim.auth'     => \App\Http\Middleware\EnsureSimAuthenticated::class,
            'plan.feature' => \App\Http\Middleware\CheckPlanFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for all API route exceptions — never expose stack traces
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof NotFoundHttpException) {
                    return response()->json(['message' => 'Resource not found.'], 404);
                }
                if ($e instanceof MethodNotAllowedHttpException) {
                    return response()->json(['message' => 'Method not allowed.'], 405);
                }
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json(['message' => 'Unauthorized.'], 403);
                }
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors'  => $e->errors(),
                    ], 422);
                }
                // Generic 500 — log full details, return safe message
                \Illuminate\Support\Facades\Log::error('Unhandled API exception', [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'url'       => $request->fullUrl(),
                    'ip'        => $request->ip(),
                ]);

                return response()->json(['message' => 'An unexpected error occurred. Please try again later.'], 500);
            }
        });
    })->create();
