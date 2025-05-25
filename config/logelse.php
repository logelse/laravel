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
    'api_url' => env('LOGELSE_API_URL', 'https://logelse-logelse-go-app.9c9iae.easypanel.host/logs'),

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
];
