<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Convert string role to UserRole enum
        $requiredRole = UserRole::tryFrom($role);

        if (!$requiredRole) {
            return response()->json([
                'message' => 'Invalid role specified.',
            ], 500);
        }

        // Check if user has the required role
        if ($request->user()->role !== $requiredRole) {
            return response()->json([
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
