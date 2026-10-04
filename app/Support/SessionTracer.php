<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/* feature-10032026-Maurice: correlation-safe operational tracing. */
class SessionTracer
{
    /* security-10042026-Maurice: password, token, api_key, email, phone, address, payload, and request values are denied. */
    protected static $context = array();

    public static function validId($candidate)
    {
        return is_string($candidate) && strlen($candidate) >= 1 && strlen($candidate) <= 64 && preg_match('/^[A-Za-z0-9._-]+$/', $candidate) === 1;
    }

    public static function setContext(array $context)
    {
        self::$context = array('correlation_id' => isset($context['correlation_id']) ? self::id($context['correlation_id']) : self::id());
    }

    public static function context()
    {
        return self::$context;
    }

    public static function clearContext()
    {
        self::$context = array();
    }

    public static function id($candidate = null)
    {
        if ($candidate !== null) {
            return self::validId($candidate) ? $candidate : self::id();
        }
        return function_exists('random_bytes') ? bin2hex(random_bytes(8)) : uniqid('', true);
    }

    public static function enter($operation, $correlationId, array $metadata = array())
    {
        return self::write('entry', $operation, $correlationId, $metadata);
    }

    public static function leave($operation, $correlationId, $startedAt, $outcome, array $metadata = array())
    {
        $metadata['outcome'] = $outcome;
        $metadata['duration_ms'] = (int) round((microtime(true) - $startedAt) * 1000);
        return self::write('exit', $operation, $correlationId, $metadata);
    }

    public static function exception($operation, $correlationId, $startedAt, $category, $exception = null)
    {
        $metadata = array('error_category' => $category, 'exception_class' => $exception ? get_class($exception) : 'unknown', 'error_class' => $exception ? get_class($exception) : 'unknown', 'safe_message' => 'operation_failed', 'message_category' => 'operation_failed');
        $metadata['level'] = $category === 'academic_credentials_invalid' ? 'warning' : 'error';
        if ($exception) {
            $metadata['stack'] = self::safeStack($exception);
            $metadata['cause'] = $exception->getPrevious() ? get_class($exception->getPrevious()) : null;
        }
        $record = self::leave($operation, $correlationId, $startedAt, 'exception', $metadata);
        if ($category === 'academic_credentials_invalid') {
            Log::warning('academic_provider_failure', $record);
        } else {
            Log::error('academic_provider_failure', $record);
        }
        return $record;
    }

    protected static function write($event, $operation, $correlationId, array $metadata)
    {
        $record = array(
            'timestamp' => gmdate('c'),
            'level' => isset($metadata['level']) ? $metadata['level'] : ($event === 'exception' ? 'error' : 'info'),
            'event' => $event,
            'operation' => $operation,
            'component' => strpos($operation, '.') !== false ? substr($operation, 0, strpos($operation, '.')) : 'application',
            'status' => isset($metadata['outcome']) ? $metadata['outcome'] : ($event === 'entry' ? 'begin' : 'recorded'),
            'correlation_id' => self::validId($correlationId) ? $correlationId : self::id(),
        );
        /* security-10032026-Maurice: only bounded authorization metadata is admitted. */
        foreach (array('outcome', 'duration_ms', 'error_category', 'exception_class', 'error_class', 'safe_message', 'message_category', 'stack', 'cause', 'level', 'role', 'resource_type', 'resource_id', 'decision', 'status') as $key) {
            if (isset($metadata[$key])) {
                $record[$key] = in_array($key, array('resource_id', 'status'), true) && is_numeric($metadata[$key]) ? (int) $metadata[$key] : substr((string) $metadata[$key], 0, 2048);
            }
        }
        $path = getenv('HOME') . '/.cache/agent-trace/ojt-tracker-management-system/' . (getenv('AGENT_SESSION_ID') ?: 'default') . '.jsonl';
        /* fix-10032026-Maurice: create only the approved cache path before writing. */
        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0700, true);
        }
        if (@is_dir(dirname($path))) {
            @file_put_contents($path, json_encode($record) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        /* observability-10042026-Maurice: normal trace envelopes stay in the
         * session artifact; only exceptions reach the application logger. */
        return $record;
    }

    protected static function safeStack($exception)
    {
        $frames = array();
        foreach ($exception->getTrace() as $frame) {
            $frames[] = (isset($frame['class']) ? $frame['class'] : '') . (isset($frame['type']) ? $frame['type'] : '') . (isset($frame['function']) ? $frame['function'] : '');
        }
        return substr(implode('|', $frames), 0, 4096);
    }
}
