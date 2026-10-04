<?php

namespace App\Http\Middleware;

use App\Support\SessionTracer;
use Illuminate\Support\Facades\Log;

/* feature/fix/observability-10042026-Maurice: trace login outcomes without identity or credentials. */
class AuthenticationObservability
{
    public function handle($request, \Closure $next)
    {
        $id = SessionTracer::id($request->header('X-Correlation-ID'));
        $started = microtime(true);
        SessionTracer::enter('auth.login.request', $id, array('component' => 'auth'));
        try {
            $response = $next($request);
            $status = $request->isMethod('post') && $request->is('login') ? 'attempted' : 'ignored';
            if ($status === 'attempted') {
                Log::info('authentication_attempt', array('event' => 'authentication_attempt', 'operation' => 'auth.login', 'component' => 'auth', 'status' => 'attempted', 'correlation_id' => $id, 'message_category' => 'credentials_not_recorded'));
            }
            SessionTracer::leave('auth.login.request', $id, $started, $status, array('component' => 'auth'));
            return $response;
        } catch (\Throwable $exception) {
            SessionTracer::exception('auth.login.request', $id, $started, 'auth_request_failed', $exception);
            throw $exception;
        }
    }
}
