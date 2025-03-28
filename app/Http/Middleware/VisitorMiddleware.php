<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VisitorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        
        if (!$user || !$user->visitor) {
            return response()->json([
                'success' => false,
                'error' => 'Only visitors can access this endpoint'
            ], 403);
        }

        // Assign visitor to request context
        $request->merge([
            'context' => [
                'visitor' => $user->visitor
            ]
        ]);

        return $next($request);
    }
} 