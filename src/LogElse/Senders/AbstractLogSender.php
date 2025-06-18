<?php

namespace LogElse\Senders;

use Illuminate\Support\Carbon;

abstract class AbstractLogSender
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Send log data to LogElse API.
     *
     * @param array $logData
     * @return void
     */
    abstract public function send(array $logData): void;

    /**
     * Format log record into the structure expected by LogElse API.
     *
     * @param array $record
     * @return array
     */
    public function formatLogData(array $record): array
    {
        return [
            'timestamp' => $this->formatTimestamp($record['datetime']),
            'log_level' => strtoupper($record['level_name']),
            'message' => $record['message'],
            'app_name' => $this->config['app_name'],
            'app_uuid' => $this->config['app_uuid'] ?? 'TEST-1',
        ];
    }

    /**
     * Format the timestamp to ISO 8601 format.
     *
     * @param \DateTimeInterface $dateTime
     * @return string
     */
    protected function formatTimestamp(\DateTimeInterface $dateTime): string
    {
        return Carbon::instance($dateTime)->toIso8601ZuluString();
    }

    /**
     * Format the context data.
     *
     * @param array $record
     * @return array
     */
    protected function formatContext(array $record): array
    {
        $context = $record['context'] ?? [];

        // Remove any sensitive data or objects that can't be serialized
        return $this->sanitizeContext($context);
    }

    /**
     * Sanitize the context data to ensure it can be serialized to JSON.
     *
     * @param array $context
     * @return array
     */
    protected function sanitizeContext(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            // Skip null values
            if ($value === null) {
                continue;
            }

            // Handle arrays recursively
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
                continue;
            }

            // Handle objects
            if (is_object($value)) {
                // If the object can be cast to string, use that
                if (method_exists($value, '__toString')) {
                    $sanitized[$key] = (string) $value;
                    continue;
                }

                // For other objects, just store the class name
                $sanitized[$key] = get_class($value);
                continue;
            }

            // Handle resources
            if (is_resource($value)) {
                $sanitized[$key] = get_resource_type($value);
                continue;
            }

            // For scalar values, just use them directly
            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
}
