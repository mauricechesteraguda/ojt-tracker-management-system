<?php

namespace App\Logging;

use App\Support\SessionTracer;

/* feature/fix/observability-10042026-Maurice: attach request correlation without payloads. */
class CorrelationProcessor
{
    public function __invoke(array $record)
    {
        $context = SessionTracer::context();
        if ($context) {
            $record['context'] = array_merge($context, isset($record['context']) ? $record['context'] : array());
        }
        return $record;
    }
}
