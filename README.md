# LogElse Laravel SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/logelse/laravel.svg?style=flat-square)](https://packagist.org/packages/logelse/laravel)
[![Apache 2.0 Licensed](https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg?style=flat-square)](LICENSE)

A Laravel package for seamlessly sending application logs to the LogElse API service. This package integrates with Laravel's logging system to provide real-time log monitoring and analysis.

## Features

- 🚀 **Easy Integration** - Seamlessly integrates with Laravel's logging system
- 🔧 **Flexible Configuration** - Environment-based configuration with sensible defaults
- 📊 **Multiple Usage Patterns** - Use as default logger, dedicated channel, or in logging stacks
- 🛡️ **Error Resilient** - Graceful handling of API failures without affecting your application
- 🔄 **Context Sanitization** - Automatically handles complex data types and sensitive information
- ⚡ **Flexible Performance** - Choose between direct (synchronous) or queue-based (asynchronous) logging

## Requirements

- **PHP**: ^7.4 | ^8.0 | ^8.1 | ^8.2
- **Laravel**: ^8.0 | ^9.0 | ^10.0 | ^11.0
- **Guzzle HTTP**: ^7.0

## Installation

Install the package via Composer:

```bash
composer require logelse/laravel
```

The package will automatically register its service provider thanks to Laravel's package auto-discovery.

## Configuration

### 1. Publish Configuration File

Publish the configuration file to customize settings:

```bash
php artisan vendor:publish --provider="LogElse\LogElseServiceProvider" --tag="config"
```

This creates a `config/logelse.php` file in your application.

### 2. Environment Variables

Configure the package by adding these environment variables to your `.env` file:

```env
# Required: Your LogElse API key
LOGELSE_API_KEY=your-api-key-here

# Optional: Custom API URL (uses default if not specified)
LOGELSE_API_URL=https://your-custom-logelse-api.com/logs

# Optional: Custom application name (uses APP_NAME if not specified)
LOGELSE_APP_NAME=MyAwesomeApp
```

### 3. Choose Logging Mode

The package supports two logging modes:

#### Direct Mode (Default)
Sends logs immediately via HTTP. Simple setup, but blocks request until log is sent.

```env
LOGELSE_MODE=direct
LOGELSE_DIRECT_TIMEOUT=5
LOGELSE_DIRECT_CONNECT_TIMEOUT=2
```

#### Queue Mode (Recommended for Production)
Sends logs via Laravel queues for better performance. Requires queue setup.

```env
LOGELSE_MODE=queue
LOGELSE_QUEUE_CONNECTION=database  # or redis if available
LOGELSE_QUEUE_NAME=logelse
LOGELSE_QUEUE_DELAY=0
LOGELSE_QUEUE_MAX_TRIES=3
LOGELSE_QUEUE_RETRY_AFTER=60
```

**Queue Setup Requirements:**
```bash
# For database queue (recommended default)
php artisan queue:table
php artisan migrate

# Make sure you have a queue worker running
php artisan queue:work --queue=logelse

# Or use Supervisor for production
```

**Automatic Fallback:** If queue mode fails (Redis not available, queue misconfigured, etc.), the package automatically falls back to direct mode to ensure logs are still sent.

### 4. Configure Logging Channel

Add the LogElse channel to your `config/logging.php` file:

```php
'channels' => [
    // ... other channels

    'logelse' => [
        'driver' => 'logelse',
        'level' => env('LOG_LEVEL', 'debug'),
        // Optional: Override global config for this channel
        // 'mode' => 'queue', // Override mode for this channel
        // 'api_key' => 'channel-specific-key',
        // 'api_url' => 'https://custom-url.com/logs',
        // 'app_name' => 'ChannelSpecificApp',
    ],

    // ... other channels
],
```

## Usage

### Basic Usage

Use LogElse as a specific logging channel:

```php
use Illuminate\Support\Facades\Log;

// Log with context
Log::channel('logelse')->info('User logged in', [
    'user_id' => 123,
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...'
]);

// Different log levels
Log::channel('logelse')->debug('Debug information');
Log::channel('logelse')->warning('Something might be wrong');
Log::channel('logelse')->error('An error occurred', ['error' => $exception->getMessage()]);
Log::channel('logelse')->critical('Critical system failure');
```

### Using as Default Channel

Set LogElse as your default logging channel:

```env
LOG_CHANNEL=logelse
```

Then use the Log facade directly:

```php
Log::info('This goes to LogElse by default');
Log::error('Error message', ['context' => 'additional data']);
```

### Using in a Logging Stack

Combine LogElse with other logging channels:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'logelse'],
        'ignore_exceptions' => false,
    ],
],
```

### Advanced Usage Examples

```php
// Log with rich context
Log::channel('logelse')->info('Order processed', [
    'order_id' => $order->id,
    'customer' => [
        'id' => $customer->id,
        'email' => $customer->email,
    ],
    'items_count' => $order->items->count(),
    'total_amount' => $order->total,
]);

// Log exceptions with context
try {
    // Some risky operation
    $result = $service->performOperation();
} catch (Exception $e) {
    Log::channel('logelse')->error('Operation failed', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'operation' => 'performOperation',
        'user_id' => auth()->id(),
    ]);
    
    throw $e;
}

// Log performance metrics
$startTime = microtime(true);
// ... some operation
$endTime = microtime(true);

Log::channel('logelse')->info('Performance metric', [
    'operation' => 'database_query',
    'duration_ms' => round(($endTime - $startTime) * 1000, 2),
    'query_type' => 'SELECT',
]);
```

## Log Format

The package sends logs to the LogElse API in the following structured JSON format:

```json
{
    "timestamp": "2024-01-15T10:30:45Z",
    "log_level": "INFO",
    "message": "User logged in successfully",
    "app_name": "MyLaravelApp",
    "context": {
        "user_id": 123,
        "ip_address": "192.168.1.1",
        "session_id": "abc123def456"
    }
}
```

### Context Data Handling

The package automatically sanitizes context data to ensure safe JSON serialization:

- **Objects**: Converted to class names or string representation if `__toString()` exists
- **Resources**: Converted to resource type names
- **Arrays**: Recursively processed
- **Null values**: Filtered out
- **Scalar values**: Passed through unchanged

## Configuration Reference

The `config/logelse.php` file contains the following options:

```php
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
        'connection' => env('LOGELSE_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('LOGELSE_QUEUE_NAME', 'logelse'),
        'delay' => env('LOGELSE_QUEUE_DELAY', 0),
        'retry_after' => env('LOGELSE_QUEUE_RETRY_AFTER', 60),
        'max_tries' => env('LOGELSE_QUEUE_MAX_TRIES', 3),
    ],
];
```

### Mode Comparison

| Feature | Direct Mode | Queue Mode |
|---------|-------------|------------|
| **Performance** | Blocks request | Non-blocking |
| **Setup Complexity** | Simple | Requires queue workers |
| **Reliability** | Immediate failure | Retry mechanism |
| **Resource Usage** | Low | Higher (queue storage) |
| **Best For** | Development, low-traffic | Production, high-traffic |

## Error Handling

The package is designed to be resilient and will not cause your application to fail if the LogElse API is unavailable. Failed log transmissions are silently ignored to ensure your application continues to function normally.

For debugging connection issues, you can temporarily enable Laravel's debug mode and check the logs for any Guzzle HTTP exceptions.

## Testing

To test the integration in your development environment:

```php
// In a controller or artisan command
Log::channel('logelse')->info('Test log from ' . config('app.name'), [
    'environment' => app()->environment(),
    'timestamp' => now()->toISOString(),
    'test' => true,
]);
```

## Compatibility

| Laravel Version | PHP Version | Package Version |
|----------------|-------------|-----------------|
| 8.x            | 7.4, 8.0+   | ^1.0           |
| 9.x            | 8.0+        | ^1.0           |
| 10.x           | 8.1+        | ^1.0           |
| 11.x           | 8.2+        | ^1.0           |

## Troubleshooting

### Common Issues

**1. Logs not appearing in LogElse**
- Verify your API key is correct
- Check that the API URL is accessible
- Ensure the log level is appropriate

**2. Configuration not loading**
- Run `php artisan config:cache` after making changes
- Verify environment variables are set correctly

**3. Performance concerns**
- Use queue mode for better performance in production
- Direct mode blocks requests until HTTP call completes
- Failed requests are handled gracefully without crashing your app

### Debug Mode

To enable verbose logging for debugging:

```php
// Temporarily add to a service provider or controller
Log::channel('logelse')->debug('Debug test', [
    'config' => config('logelse'),
    'environment' => app()->environment(),
]);
```

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Check code style: `composer cs-check`

## Security

If you discover any security-related issues, please email security@logelse.com instead of using the issue tracker.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The Apache License 2.0. Please see [License File](LICENSE) for more information.

## Support

- 📧 **Email**: support@logelse.com
- 📖 **Documentation**: [https://docs.logelse.com](https://docs.logelse.com)
- 🐛 **Issues**: [GitHub Issues](https://github.com/logelse/laravel/issues)

---

Made with ❤️ by the LogElse team
