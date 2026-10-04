<?php

namespace App\Http\Middleware;

use App\Support\SessionTracer;
use Illuminate\Support\Facades\Log;

/* feature/fix/observability-10042026-Maurice: establish one safe ID at the HTTP boundary. */
class CorrelationId
{
    public function handle($request, \Closure $next)
    {
        $candidate = $request->header('X-Correlation-ID');
        $id = SessionTracer::validId($candidate) ? $candidate : SessionTracer::id();
        SessionTracer::setContext(array('correlation_id' => $id));
        $request->headers->set('X-Correlation-ID', $id);
        $request->attributes->set('correlation_id', $id);
        $started = microtime(true);
        SessionTracer::enter('http.correlation', $id, array('component' => 'http'));
        try {
            $response = $next($request);
            $response = $response->header('X-Correlation-ID', $id);
            Log::info('http_request', array('event' => 'http_request', 'component' => 'http', 'operation' => 'request', 'status' => (string) $response->getStatusCode(), 'correlation_id' => $id, 'message_category' => 'request_completed'));
            SessionTracer::leave('http.correlation', $id, $started, 'success', array('component' => 'http'));
            return $response;
        } catch (\Throwable $exception) {
            SessionTracer::exception('http.correlation', $id, $started, 'http_request_failed', $exception);
            throw $exception;
        } finally {
            SessionTracer::clearContext();
        }
    }
}
