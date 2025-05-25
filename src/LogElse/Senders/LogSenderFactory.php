<?php

namespace LogElse\Senders;

use InvalidArgumentException;

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
     * Get available logging modes.
     *
     * @return array
     */
    public static function getAvailableModes(): array
    {
        return ['direct', 'queue'];
    }
}
