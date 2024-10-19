<?php
namespace App\Core;

use Symfony\Component\HttpFoundation\RequestStack;

class ServiceContainer
{
    private $services = [];

    public function __construct()
    {
        // Mendaftarkan service yang diperlukan
        $this->services['request_stack'] = new RequestStack(); // Inisialisasi RequestStack
        $this->services['user_handler'] = new \Modules\User\Handlers\UserHandler($this->services['request_stack']);
    }

    public function get($service)
    {
        if (!isset($this->services[$service])) {
            throw new \Exception("Service {$service} not found.");
        }

        return $this->services[$service];
    }
}
