<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/* feature-10032026-Maurice: correlation-safe operational tracing. */
class SessionTracer
{
    public static function id($candidate = null)
    {
        if ($candidate !== null) {
            $candidate = preg_replace('/[^A-Za-z0-9._-]/', '', (string) $candidate);
            return $candidate !== '' && strlen($candidate) <= 64 ? $candidate : 'invalid-correlation';
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
        $metadata = array('error_category' => $category, 'exception_class' => $exception ? get_class($exception) : 'unknown', 'safe_message' => 'Academic provider operation failed.');
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
        $record = array('event' => $event, 'operation' => $operation, 'correlation_id' => $correlationId);
        foreach (array('outcome', 'duration_ms', 'error_category', 'exception_class', 'safe_message', 'stack', 'cause', 'level') as $key) {
            if (isset($metadata[$key])) {
                $record[$key] = $metadata[$key];
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
        if ($event !== 'exception' && (!isset($record['outcome']) || $record['outcome'] !== 'exception')) {
            Log::info('academic_provider_operation', $record);
        }
        return $record;
    }

    protected static function safeStack($exception)
    {
        $frames = array();
        foreach ($exception->getTrace() as $frame) {
            $frames[] = (isset($frame['class']) ? $frame['class'] : '') . (isset($frame['type']) ? $frame['type'] : '') . (isset($frame['function']) ? $frame['function'] : '');
        }
        return implode('|', $frames);
    }
}
