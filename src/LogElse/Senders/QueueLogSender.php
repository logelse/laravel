<?php

namespace LogElse\Senders;

use LogElse\Jobs\SendLogElseJob;
use Exception;
use Illuminate\Support\Facades\Log;

class QueueLogSender extends AbstractLogSender
{
    /**
     * @var DirectLogSender|null
     */
    protected $fallbackSender;

    /**
     * Send log data to LogElse API via Laravel queues.
     *
     * @param array $logData
     * @return void
     */
    public function send(array $logData): void
    {
        try {
            $job = SendLogElseJob::dispatch($logData, $this->config)
                ->onConnection($this->config['queue']['connection'] ?? 'default')
                ->onQueue($this->config['queue']['queue_name'] ?? 'logelse');

            // Apply delay if configured
            if (isset($this->config['queue']['delay']) && $this->config['queue']['delay'] > 0) {
                $job->delay($this->config['queue']['delay']);
            }
        } catch (Exception $e) {
            // Queue failed (Redis not available, queue misconfigured, etc.)
            // Fall back to direct mode to ensure logs are still sent
            $this->handleQueueFailure($logData, $e);
        }
    }

    /**
     * Handle queue failure by falling back to direct mode.
     *
     * @param array $logData
     * @param Exception $exception
     * @return void
     */
    protected function handleQueueFailure(array $logData, Exception $exception): void
    {
        try {
            // Log the queue failure (but avoid infinite recursion)
            if (!$this->isLogElseRelatedError($exception)) {
                Log::channel('single')->warning('LogElse queue mode failed, falling back to direct mode', [
                    'error' => $exception->getMessage(),
                    'queue_connection' => $this->config['queue']['connection'] ?? 'default',
                    'fallback_mode' => 'direct'
                ]);
            }

            // Create fallback sender if not exists
            if (!$this->fallbackSender) {
                $fallbackConfig = array_merge($this->config, [
                    'mode' => 'direct',
                    'direct' => $this->config['direct'] ?? [
                        'timeout' => 5,
                        'connect_timeout' => 2,
                    ]
                ]);
                $this->fallbackSender = new DirectLogSender($fallbackConfig);
            }

            // Send via direct mode
            $this->fallbackSender->send($logData);
        } catch (Exception $fallbackException) {
            // Even fallback failed - silently ignore to prevent application crashes
            // This maintains the same behavior as the original direct mode
        }
    }

    /**
     * Check if the error is related to LogElse to avoid infinite recursion.
     *
     * @param Exception $exception
     * @return bool
     */
    protected function isLogElseRelatedError(Exception $exception): bool
    {
        $message = $exception->getMessage();
        $trace = $exception->getTraceAsString();
        
        return strpos($message, 'LogElse') !== false || 
               strpos($trace, 'LogElse') !== false ||
               strpos($trace, 'logelse') !== false;
    }
}
