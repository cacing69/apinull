<?php

namespace App\Core;
use App\Core\ServiceContainer;

class BaseHandler
{
    protected $logger;
    protected $request;

    public function __construct(ServiceContainer $container)
    {
        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();

        $this->request = $container->get('request'); // Mengambil Request dari container
    }
}
