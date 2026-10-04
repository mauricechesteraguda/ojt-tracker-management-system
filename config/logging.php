<?php

use Monolog\Handler\StreamHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['stderr', 'structured_file'],
        ],

        'structured_file' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'level' => 'debug',
            'with' => ['stream' => storage_path('logs/laravel.log')],
        ],

        'single' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'level' => 'debug',
            'with' => ['stream' => 'php://stderr'],
        ],

        'daily' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'level' => 'debug',
            'with' => ['stream' => 'php://stderr'],
        ],

        'slack' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'level' => 'critical',
            'with' => ['stream' => 'php://stderr'],
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'level' => 'debug',
            'with' => ['stream' => 'php://stderr'],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'level' => 'debug',
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'level' => 'debug',
            'with' => ['stream' => 'php://stderr'],
        ],

        'errorlog' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => App\Logging\JsonFormatter::class,
            'tap' => [App\Logging\ConfigureStructuredLogging::class],
            'level' => 'debug',
            'with' => ['stream' => 'php://stderr'],
        ],
    ],

];
