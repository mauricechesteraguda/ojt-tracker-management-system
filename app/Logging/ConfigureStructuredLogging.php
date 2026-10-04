<?php

namespace App\Logging;

use Monolog\Logger;

/* feature/fix/observability-10042026-Maurice: configure every stderr handler consistently. */
class ConfigureStructuredLogging
{
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
            $handler->pushProcessor(new CorrelationProcessor());
        }
    }
}
