<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ProviderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        
        if (!$user || !$user->provider) {
            Log::info('ProviderMiddleware: Access denied', [
                'user_id' => $user ? $user->id : null,
                'has_provider' => $user && $user->provider ? 'yes' : 'no',
                'token' => $request->bearerToken()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Only providers can access this endpoint',
                'debug' => [
                    'user_id' => $user ? $user->id : null,
                    'has_provider' => $user && $user->provider ? 'yes' : 'no'
                ]
            ], 403);
        }

        // Assign provider to request context
        $request->merge([
            'context' => [
                'provider' => $user->provider
            ]
        ]);

        return $next($request);
    }
} 