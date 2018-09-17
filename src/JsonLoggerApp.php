<?php

namespace DennyLoko\Laravel\Lumen;

use Monolog\Logger;
use Laravel\Lumen\Application;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;

class JsonLoggerApp extends Application
{
    private $appName;

    /**
     * Create a new Lumen application instance.
     *
     * @param string      $appName
     * @param string|null $basePath
     */
    public function __construct($appName, $basePath = null)
    {
        parent::__construct($basePath);

        $this->appName = $appName;
    }

    /**
     * This replaces the inline array used in \Lumen\Application to allow multiple handlers.
     */
    protected function registerLogBindings()
    {
        $this->singleton('Psr\Log\LoggerInterface', function () {
            $logger = new Logger($this->appName, $this->getMonologHandler());

            $logger->pushProcessor(function ($record) {
                $record['source'] = 'php';

                return $record;
            });

            return $logger;
        });
    }

    /**
     * Extends the default logging implementation with additional handlers if configured in .env.
     *
     * @return array of type \Monolog\Handler\AbstractHandler
     */
    protected function getMonologHandler()
    {
        $handlers = [];
        $handlers[] = (new StreamHandler(
            storage_path('logs/lumen.log'),
            Logger::DEBUG)
            )->setFormatter(new LineFormatter(null, null, true, true)
        );

        $handlers[] = (
            new StreamHandler(
                'php://stdout',
                Logger::DEBUG
            )
        )->setFormatter(new JsonFormatter());

        return $handlers;
    }
}
