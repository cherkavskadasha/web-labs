<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    private const HIERARCHY = [
        'ROLE_CLIENT'  => 1,
        'ROLE_MANAGER' => 2,
        'ROLE_ADMIN'   => 3,
    ];

    /**
     * @param Request $request
     * @param Closure $next
     * @param string $requiredRole
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $userLevel = self::HIERARCHY[$user->role] ?? 0;
        $requiredLevel = self::HIERARCHY[$requiredRole] ?? 99;

        if ($userLevel < $requiredLevel) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}