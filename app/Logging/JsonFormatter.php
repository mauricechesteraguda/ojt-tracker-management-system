<?php

namespace App\Logging;

use Monolog\Formatter\FormatterInterface;

/* feature/fix/observability-10042026-Maurice: bounded JSONL for stderr. */
class JsonFormatter implements FormatterInterface
{
    public function format(array $record)
    {
        $context = isset($record['context']) && is_array($record['context']) ? $record['context'] : array();
        $item = array(
            'timestamp' => isset($record['datetime']) ? $record['datetime']->format('c') : gmdate('c'),
            'level' => strtolower(isset($record['level_name']) ? $record['level_name'] : 'info'),
            /* security-10042026-Maurice: never serialize Monolog's raw message. */
            'event' => isset($context['event']) ? (string) $context['event'] : 'log_recorded',
            'component' => isset($context['component']) ? (string) $context['component'] : 'laravel',
            'operation' => isset($context['operation']) ? (string) $context['operation'] : 'log',
            'status' => isset($context['status']) ? (string) $context['status'] : 'recorded',
        );
        foreach (array('correlation_id', 'exception_class', 'error_class', 'error_category', 'safe_message', 'message_category', 'stack', 'cause') as $key) {
            if (isset($context[$key])) {
                $item[$key] = $this->bounded($context[$key]);
            }
        }
        return json_encode($item, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    public function formatBatch(array $records)
    {
        $output = '';
        foreach ($records as $record) {
            $output .= $this->format($record);
        }
        return $output;
    }

    protected function bounded($value)
    {
        if (is_array($value)) {
            return array_slice($value, 0, 8, true);
        }
        return substr((string) $value, 0, 2048);
    }
}
