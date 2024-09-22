<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('sanctum')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized! Please login to access the resource.'
            ], 401);
        }

        return $next($request);
    }
}
