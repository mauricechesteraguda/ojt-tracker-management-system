<?php

namespace App\Services;

use App\Internship;
use App\Support\SessionTracer;
use Illuminate\Support\Facades\Log;

/* security-10032026-Maurice: one ownership decision point for API resources. */
class AuthorizationService
{
    public function canAccessInternship($user, Internship $internship)
    {
        $started = microtime(true);
        $id = SessionTracer::id(request()->header('X-Correlation-ID'));
        $role = $user ? (string) $user->role : '';
        $allowed = in_array($role, array('coordinator', 'superuser'), true) || ($user && (int) $internship->user_id === (int) $user->id);
        SessionTracer::enter('authorization.internship', $id, array('role' => $role, 'resource_type' => 'internship', 'resource_id' => (string) $internship->id));
        try {
            Log::info('authorization_decision', array('correlation_id' => $id, 'operation' => 'access', 'actor_role' => $role, 'resource_type' => 'internship', 'resource_id' => (string) $internship->id, 'decision' => $allowed ? 'allow' : 'deny'));
            SessionTracer::leave('authorization.internship', $id, $started, $allowed ? 'allowed' : 'denied', array('role' => $role, 'resource_type' => 'internship', 'resource_id' => (string) $internship->id));
            return $allowed;
        } catch (\Throwable $exception) {
            SessionTracer::exception('authorization.internship', $id, $started, 'authorization_unexpected', $exception);
            throw $exception;
        }
    }
}
