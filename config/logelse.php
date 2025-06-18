<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LogElse API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key used to authenticate with the LogElse API.
    |
    */
    'api_key' => env('LOGELSE_API_KEY', 'SUPER_SECRET_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | LogElse API URL
    |--------------------------------------------------------------------------
    |
    | This is the URL of the LogElse API.
    |
    */
    'api_url' => 'https://ingst.logelse.com/logs',

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a log message.
    |
    */
    'app_name' => env('LOGELSE_APP_NAME', env('APP_NAME', 'Laravel')),

    /*
    |--------------------------------------------------------------------------
    | Application UUID
    |--------------------------------------------------------------------------
    |
    | This value is the unique identifier of your application. This value is used
    | to identify your application in the LogElse dashboard.
    |
    */
    'app_uuid' => env('LOGELSE_APP_UUID', 'TEST-1'),

    /*
    |--------------------------------------------------------------------------
    | Logging Mode
    |--------------------------------------------------------------------------
    |
    | This determines how logs are sent to the LogElse API:
    | - 'direct': Sends logs immediately via HTTP (synchronous)
    | Queue mode is currently disabled.
    |
    */
    'mode' => 'direct',

    /*
    |--------------------------------------------------------------------------
    | Direct Mode Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for direct mode logging.
    |
    */
    'direct' => [
        'timeout' => env('LOGELSE_DIRECT_TIMEOUT', 5),
        'connect_timeout' => env('LOGELSE_DIRECT_CONNECT_TIMEOUT', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Mode Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for queue-based logging.
    | Note: If queue mode fails (Redis not available, etc.), it will
    | automatically fall back to direct mode to ensure logs are still sent.
    |
    */
    'queue' => [
        'connection' => env('LOGELSE_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('LOGELSE_QUEUE_NAME', 'logelse'),
        'delay' => env('LOGELSE_QUEUE_DELAY', 0),
        'retry_after' => env('LOGELSE_QUEUE_RETRY_AFTER', 60),
        'max_tries' => env('LOGELSE_QUEUE_MAX_TRIES', 3),
    ],
];
