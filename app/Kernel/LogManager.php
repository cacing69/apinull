<?php

namespace App\Kernel;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogManager
{
    private $logger;

    public function __construct()
    {
        // Membuat instance Logger
        $this->logger = new Logger('api_logger');

        // Menambahkan handler untuk mencatat ke file
        // $this->logger->pushHandler(new StreamHandler(app_path('/logs/api-'.date('Ymd').'.log'), Logger::DEBUG));
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}
