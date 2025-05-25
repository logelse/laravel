<?php

return [
    // LogElse API key for authentication
    'api_key' => env('LOGELSE_API_KEY', 'your-default-key'),
    
    // LogElse API endpoint URL
    'api_url' => env('LOGELSE_API_URL', 'https://api.logelse.com/logs'),
    
    // Application name for log identification
    'app_name' => env('LOGELSE_APP_NAME', env('APP_NAME', 'Laravel')),
    
    // Logging mode: 'direct' or 'queue'
    'mode' => env('LOGELSE_MODE', 'direct'),
    
    // Direct mode configuration
    'direct' => [
        'timeout' => env('LOGELSE_DIRECT_TIMEOUT', 5),
        'connect_timeout' => env('LOGELSE_DIRECT_CONNECT_TIMEOUT', 2),
    ],
    
    // Queue mode configuration
    'queue' => [
        'connection' => env('LOGELSE_QUEUE_CONNECTION', 'default'),
        'queue_name' => env('LOGELSE_QUEUE_NAME', 'logelse'),
        'delay' => env('LOGELSE_QUEUE_DELAY', 0),
        'retry_after' => env('LOGELSE_QUEUE_RETRY_AFTER', 60),
        'max_tries' => env('LOGELSE_QUEUE_MAX_TRIES', 3),
    ],
];