<?php

namespace LogElse\Senders;

use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class LogSenderFactory
{
    /**
     * Create a log sender instance based on configuration.
     *
     * @param array $config
     * @return AbstractLogSender
     * @throws InvalidArgumentException
     */
    public static function create(array $config): AbstractLogSender
    {
        $mode = $config['mode'] ?? 'direct';

        // Validate queue configuration if queue mode is requested
        if ($mode === 'queue' && !static::isQueueConfigurationValid($config)) {
            Log::channel('single')->warning('LogElse queue mode requested but configuration invalid, falling back to direct mode', [
                'requested_mode' => 'queue',
                'fallback_mode' => 'direct',
                'queue_connection' => $config['queue']['connection'] ?? 'default'
            ]);
            $mode = 'direct';
        }

        switch ($mode) {
            case 'queue':
                return new QueueLogSender($config);
            
            case 'direct':
                return new DirectLogSender($config);
            
            default:
                throw new InvalidArgumentException("Unsupported LogElse mode: {$mode}. Supported modes are: direct, queue");
        }
    }

    /**
     * Check if queue configuration is valid.
     *
     * @param array $config
     * @return bool
     */
    protected static function isQueueConfigurationValid(array $config): bool
    {
        try {
            $connection = $config['queue']['connection'] ?? 'default';
            
            // Check if the queue connection exists in config
            $queueConfig = config("queue.connections.{$connection}");
            if (!$queueConfig) {
                return false;
            }

            // For Redis connections, check if Redis is available
            if ($queueConfig['driver'] === 'redis') {
                return class_exists('Redis') || class_exists('Predis\Client');
            }

            // For database connections, assume it's valid if configured
            if ($queueConfig['driver'] === 'database') {
                return true;
            }

            // For sync driver, it's always valid
            if ($queueConfig['driver'] === 'sync') {
                return true;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get available logging modes.
     *
     * @return array
     */
    public static function getAvailableModes(): array
    {
        return ['direct', 'queue'];
    }

    /**
     * Get recommended queue configuration for the current environment.
     *
     * @return array
     */
    public static function getRecommendedQueueConfig(): array
    {
        // Check what's available and recommend accordingly
        if (class_exists('Redis') || class_exists('Predis\Client')) {
            return [
                'connection' => 'redis',
                'queue_name' => 'logelse',
                'delay' => 0,
                'retry_after' => 60,
                'max_tries' => 3,
            ];
        }

        // Fall back to database queue
        return [
            'connection' => 'database',
            'queue_name' => 'logelse',
            'delay' => 0,
            'retry_after' => 60,
            'max_tries' => 3,
        ];
    }
}
