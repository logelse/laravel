<?php

namespace LogElse\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Exception;

class SendLogElseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter;

    /**
     * @var array
     */
    protected $logData;

    /**
     * @var array
     */
    protected $config;

    /**
     * Create a new job instance.
     *
     * @param array $logData
     * @param array $config
     */
    public function __construct(array $logData, array $config)
    {
        $this->logData = $logData;
        $this->config = $config;
        
        // Set retry configuration from config
        $this->tries = $config['queue']['max_tries'] ?? 3;
        $this->retryAfter = $config['queue']['retry_after'] ?? 60;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        $client = new Client([
            'timeout' => 30, // Longer timeout for background jobs
            'connect_timeout' => 10,
        ]);

        $client->post($this->config['api_url'], [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-KEY' => $this->config['api_key'],
            ],
            'json' => $this->logData,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Log to Laravel's default logger when LogElse fails completely
        Log::channel('single')->error('LogElse queue job failed after all retries', [
            'exception' => $exception->getMessage(),
            'log_data' => $this->logData,
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function backoff()
    {
        // Exponential backoff: 60s, 120s, 240s, etc.
        return $this->retryAfter * pow(2, $this->attempts() - 1);
    }
}
