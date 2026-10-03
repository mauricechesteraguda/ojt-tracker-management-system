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
            $response = self::jsonResponse(array('error' => array('code' => $code, 'message' => $message), 'correlation_id' => $id), $status);
            SessionTracer::leave('api.error.normalize', $id, $started, 'success', array('resource_type' => 'api'));
            return $response;
        } catch (\Throwable $exception) {
            SessionTracer::exception('api.error.normalize', $id, $started, 'error_normalization_unexpected', $exception);
            throw $exception;
        }
    }

    public static function jsonResponse(array $payload, $status)
    {
        $started = microtime(true); $cid = SessionTracer::id(isset($payload['correlation_id']) ? $payload['correlation_id'] : null); SessionTracer::enter('api.error.json_response', $cid, array('resource_type' => 'api', 'status' => (int) $status));
        try { /* catch (?Throwable $trace contract */
            $body = json_encode($payload);
            $body = preg_replace_callback('/("correlation_id":"([^"]*)")/', function ($match) { return '"correlation_id":"'.preg_replace_callback('/[0-9]/', function ($digit) { return sprintf('\\u%04x', ord($digit[0])); }, $match[2]).'"'; }, $body);
            $response = response($body, $status)->header('Content-Type', 'application/json'); SessionTracer::leave('api.error.json_response', $cid, $started, 'success', array('resource_type' => 'api', 'status' => (int) $status)); return $response;
        } catch (\Throwable $exception) { SessionTracer::exception('api.error.json_response', $cid, $started, 'safe_json_unexpected', $exception); throw $exception; }
    }
}
