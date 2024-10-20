<?php

namespace App\Core;
use App\Core\ServiceContainer;

class BaseHandler
{
    // protected $logger;
    // protected $request;

    // public function __construct(ServiceContainer $container)
    public function __construct()
    {
        // Inisialisasi logger
        // $logManager = new LogManager();
        // $this->logger = $logManager->getLogger();
        // $this->setupLogger();

        // $this->request = $container->get('request'); // Mengambil Request dari container

    }

    protected function setupLogger()
{
    $logManager = new LogManager();
    $this->logger = $logManager->getLogger();
}

    protected function validate(array $rules, array $data)
    {
    }
}
