<?php

namespace App\Kernel;

// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;

class LogManager
{
    private $logger;

    public function __construct()
    {
    }

    public function getLogger() //: Logger
    {
        return $this->logger;
    }
}
