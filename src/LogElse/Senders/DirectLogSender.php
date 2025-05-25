<?php

namespace LogElse\Senders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DirectLogSender extends AbstractLogSender
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        
        $this->client = new Client([
            'timeout' => $this->config['direct']['timeout'] ?? 5,
            'connect_timeout' => $this->config['direct']['connect_timeout'] ?? 2,
        ]);
    }

    /**
     * Send log data directly to LogElse API via HTTP.
     *
     * @param array $logData
     * @return void
     * @throws \Exception
     */
    public function send(array $logData): void
    {
        try {
            $this->client->post($this->config['api_url'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-KEY' => $this->config['api_key'],
                ],
                'json' => $logData,
            ]);
        } catch (GuzzleException $e) {
            // Silently fail - we don't want to cause issues in the application
            // due to logging problems. In direct mode, we prioritize application
            // stability over guaranteed log delivery.
            
            // Optionally, you could log to Laravel's default logger here:
            // \Log::channel('single')->debug('LogElse direct send failed: ' . $e->getMessage());
        }
    }
}
