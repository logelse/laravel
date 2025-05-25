<?php

namespace LogElse\Senders;

use LogElse\Jobs\SendLogElseJob;

class QueueLogSender extends AbstractLogSender
{
    /**
     * Send log data to LogElse API via Laravel queues.
     *
     * @param array $logData
     * @return void
     */
    public function send(array $logData): void
    {
        $job = SendLogElseJob::dispatch($logData, $this->config)
            ->onConnection($this->config['queue']['connection'] ?? 'default')
            ->onQueue($this->config['queue']['queue_name'] ?? 'logelse');

        // Apply delay if configured
        if (isset($this->config['queue']['delay']) && $this->config['queue']['delay'] > 0) {
            $job->delay($this->config['queue']['delay']);
        }
    }
}
