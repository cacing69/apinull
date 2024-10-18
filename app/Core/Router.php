<?php

namespace App\Core;

use Symfony\Component\Yaml\Yaml;

class Router
{
    private $routes;

    public function __construct($configFile)
    {
        $this->routes = Yaml::parseFile($configFile)['routes'];
    }

    public function dispatch($requestUri)
    {
        // var_dump($this->routes);
        // die();
        foreach ($this->routes as $route) {
            if ($route['path'] === $requestUri) {
                // Temukan handler yang sesuai
                $handlerClass = $route['handler'];
                $method = $route['method'];

                if (class_exists($handlerClass)) {
                    $handler = new $handlerClass();
                    return $handler->$method();
                }
            }
        }

        // Jika tidak ada rute yang cocok
        return ['error' => 'route not found'];
    }
}
