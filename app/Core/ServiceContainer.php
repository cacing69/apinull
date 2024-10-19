<?php
namespace App\Core;

use Psr\Container\ContainerInterface;

class ServiceContainer implements ContainerInterface
{
    private $services = [];

    public function set($name, $service)
    {
        $this->services[$name] = $service;
    }

    public function get($name)
    {
        if (!isset($this->services[$name])) {
            throw new \Exception("Service not found: $name");
        }
        return $this->services[$name];
    }

    public function has($name) :bool
    {
        return isset($this->services[$name]);
    }
}
