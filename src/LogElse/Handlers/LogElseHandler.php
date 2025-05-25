<?php

namespace LogElse\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;

class LogElseHandler extends AbstractProcessingHandler
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param string $apiKey
     * @param string $apiUrl
     * @param string $appName
     * @param int|string $level
     * @param bool $bubble
     */
    public function __construct(
        string $apiKey,
        string $apiUrl,
        string $appName,
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->appName = $appName;
        $this->client = new Client();
    }

    /**
     * {@inheritdoc}
     */
    protected function write($record): void
    {
        // Support for both Monolog v2 and v3
        $record = $record instanceof LogRecord ? $record->toArray() : $record;

        try {
            $this->client->post($this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-KEY' => $this->apiKey,
                ],
                'json' => [
                    'timestamp' => $this->formatTimestamp($record['datetime']),
                    'log_level' => strtoupper($record['level_name']),
                    'message' => $record['message'],
                    'app_name' => $this->appName,
                    'context' => $this->formatContext($record),
                ],
            ]);
        } catch (GuzzleException $e) {
            // Silently fail - we don't want to cause issues in the application
            // due to logging problems
        }
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
