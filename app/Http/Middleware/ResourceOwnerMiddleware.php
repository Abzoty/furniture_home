<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourceOwnerMiddleware
{
    /**
     * Handle an incoming request.
     * This middleware ensures customers can only access their own resources.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Admin can access all resources
        if ($user->role === 'admin') {
            return $next($request);
        }

        // For customers, add a filter to the request to limit to their own resources
        if ($user->role === 'customer') {
            $request->merge(['customer_id' => $user->id]);
        }

        return $next($request);
    }
}
