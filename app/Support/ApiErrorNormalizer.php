<?php

namespace App\Support;

/* security-10032026-Maurice: API errors expose only stable safe fields. */
class ApiErrorNormalizer
{
    public static function response($request, $status, $code, $correlationId = null)
    {
        $started = microtime(true);
        $id = SessionTracer::id($correlationId ?: $request->header('X-Correlation-ID'));
        SessionTracer::enter('api.error.normalize', $id, array('resource_type' => 'api', 'status' => (int) $status));
        try {
            $message = $status === 401 ? 'Unauthenticated.' : ($status === 403 ? 'Forbidden.' : ($status === 404 ? 'Not found.' : 'Invalid request.'));
            $response = response()->json(array('error' => array('code' => $code, 'message' => $message), 'correlation_id' => $id), $status);
            SessionTracer::leave('api.error.normalize', $id, $started, 'success', array('resource_type' => 'api'));
            return $response;
        } catch (\Throwable $exception) {
            SessionTracer::exception('api.error.normalize', $id, $started, 'error_normalization_unexpected', $exception);
            throw $exception;
        }
    }
}
