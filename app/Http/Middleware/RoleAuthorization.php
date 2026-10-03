<?php

namespace App\Http\Middleware;

use App\Support\SessionTracer;
use Closure;

/* security-10032026-Maurice: backend role boundary is deny-by-default. */
class RoleAuthorization
{
    public function handle($request, Closure $next, ...$allowedRoles)
    {
        $started = microtime(true);
        $correlationId = SessionTracer::id($request->header('X-Correlation-ID'));
        SessionTracer::enter('authorization.role', $correlationId, array('role' => optional($request->user())->role, 'resource_type' => 'route'));
        try {
            $user = $request->user();
            $role = $user ? (string) $user->role : '';
            $allowed = array_intersect(array('student', 'coordinator', 'superuser'), $allowedRoles);
            if (!$user || !$allowed || !in_array($role, $allowed, true)) {
                SessionTracer::leave('authorization.role', $correlationId, $started, 'denied', array('role' => $role, 'resource_type' => 'route'));
                return response()->json(array('error' => array('code' => $user ? 'forbidden' : 'unauthenticated', 'message' => $user ? 'Forbidden.' : 'Unauthenticated.'), 'correlation_id' => $correlationId), $user ? 403 : 401);
            }
            SessionTracer::leave('authorization.role', $correlationId, $started, 'allowed', array('role' => $role, 'resource_type' => 'route'));
            return $next($request);
        } catch (\Throwable $exception) {
            SessionTracer::exception('authorization.role', $correlationId, $started, 'authorization_unexpected', $exception);
            throw $exception;
        }
    }
}
