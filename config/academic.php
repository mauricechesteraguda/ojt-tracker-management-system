<?php

/* feature-10032026-Maurice: environment-only provider configuration. */

return array(
    'mode' => env('ACADEMIC_PROVIDER_MODE', 'fake'),
    'base_url' => env('ACADEMIC_PROVIDER_BASE_URL', ''),
    'key' => env('ACADEMIC_PROVIDER_KEY', ''),
    'connect_timeout' => (int) env('ACADEMIC_PROVIDER_CONNECT_TIMEOUT_SECONDS', 2),
    'timeout' => (int) env('ACADEMIC_PROVIDER_TIMEOUT_SECONDS', 5),
    'allow_local' => (bool) env('ACADEMIC_PROVIDER_ALLOW_LOCAL', false),
);
