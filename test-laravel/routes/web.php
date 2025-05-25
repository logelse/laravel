<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-logelse', function () {
    // Log messages at different levels
    Log::emergency('This is an emergency message', ['user_id' => 1]);
    Log::alert('This is an alert message', ['user_id' => 1]);
    Log::critical('This is a critical message', ['user_id' => 1]);
    Log::error('This is an error message', ['user_id' => 1]);
    Log::warning('This is a warning message', ['user_id' => 1]);
    Log::notice('This is a notice message', ['user_id' => 1]);
    Log::info('This is an info message', ['user_id' => 1]);
    Log::debug('This is a debug message', ['user_id' => 1]);

    // Log with complex context data
    Log::info('User registered', [
        'user' => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'timestamp' => now()->toIso8601String(),
    ]);

    // Return a response
    return response()->json([
        'message' => 'Logs sent to LogElse API',
        'logs' => [
            'emergency' => 'This is an emergency message',
            'alert' => 'This is an alert message',
            'critical' => 'This is a critical message',
            'error' => 'This is an error message',
            'warning' => 'This is a warning message',
            'notice' => 'This is a notice message',
            'info' => 'This is an info message',
            'debug' => 'This is a debug message',
            'complex' => 'User registered',
        ],
        'api_url' => env('LOGELSE_API_URL'),
        'app_name' => env('LOGELSE_APP_NAME'),
    ]);
});

Route::get('/test-logelse-direct', function () {
    // Use the LogElse channel directly
    Log::channel('logelse')->info('This message is sent directly to the LogElse channel', [
        'test_key' => 'test_value',
        'timestamp' => now()->toIso8601String(),
    ]);

    // Return a response
    return response()->json([
        'message' => 'Log sent directly to LogElse API',
        'log' => 'This message is sent directly to the LogElse channel',
        'api_url' => env('LOGELSE_API_URL'),
        'app_name' => env('LOGELSE_APP_NAME'),
    ]);
});
