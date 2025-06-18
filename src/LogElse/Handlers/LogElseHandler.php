<?php

namespace LogElse\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use LogElse\Senders\LogSenderFactory;
use LogElse\Senders\AbstractLogSender;
use Exception;

class LogElseHandler extends AbstractProcessingHandler
{
    /**
     * @var AbstractLogSender
     */
    protected $sender;

    /**
     * @var array
     */
    protected $config;

    /**
     * Create a new LogElse handler instance.
     *
     * @param array $config Full configuration array
     * @param int|string $level The minimum logging level
     * @param bool $bubble Whether the messages that are handled can bubble up the stack
     */
    public function __construct(array $config, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->config = $config;
        $this->sender = LogSenderFactory::create($config);
    }

    /**
     * Legacy constructor for backward compatibility.
     *
     * @param string $apiKey
     * @param string $apiUrl
     * @param string $appName
     * @param int|string $level
     * @param bool $bubble
     * @return static
     * @deprecated Use the new constructor with config array instead
     */
    public static function createLegacy(
        string $apiKey,
        string $apiUrl,
        string $appName,
        $level = Logger::DEBUG,
        bool $bubble = true
    ): self {
        $config = [
            'api_key' => $apiKey,
            'api_url' => $apiUrl,
            'app_name' => $appName,
            'app_uuid' => 'TEST-1',
            'mode' => 'direct',
            'direct' => [
                'timeout' => 5,
                'connect_timeout' => 2,
            ],
        ];

        return new self($config, $level, $bubble);
    }

    /**
     * Write a log record.
     *
     * @param array $record
     * @return void
     */
    protected function write($record): void
    {
        // Support for both Monolog v2 and v3
        $record = $record instanceof LogRecord ? $record->toArray() : $record;

        try {
            $logData = $this->sender->formatLogData($record);
            $this->sender->send($logData);
        } catch (Exception $e) {
            // Handle any unexpected errors gracefully
            // The individual senders handle their own specific error cases
        }
    }
}
