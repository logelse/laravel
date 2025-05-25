<?php

namespace LogElse;

use Illuminate\Support\ServiceProvider;
use Illuminate\Log\LogManager;
use LogElse\Handlers\LogElseHandler;

class LogElseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/logelse.php', 'logelse'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/logelse.php' => config_path('logelse.php'),
        ], 'config');

        // Extend Laravel's logging system with our custom driver
        $this->app->make(LogManager::class)->extend('logelse', function ($app, array $config) {
            $handler = new LogElseHandler(
                $config['api_key'] ?? config('logelse.api_key'),
                $config['api_url'] ?? config('logelse.api_url'),
                $config['app_name'] ?? config('logelse.app_name'),
                $config['level'] ?? 'debug'
            );
            
            $logger = new \Monolog\Logger('logelse');
            $logger->pushHandler($handler);
            
            return $logger;
        });
    }
}
