<?php

namespace App\Core;

use Symfony\Component\Yaml\Yaml;

class Router
{
    private $routes;
    private $allRoutes = [];

    public function __construct($configFile)
    {
        $this->routes = Yaml::parseFile($configFile);

            if (isset($this->routes['imports'])) {
                foreach ($this->routes['imports'] as $import) {
                    $importedRoutes = Yaml::parseFile($import['resource']);
                    // var_dump($importedRoutes);
                    // die();
                    $this->allRoutes = array_merge($this->allRoutes, $importedRoutes['routes']);
                }
            }
    }

    public function dispatch($requestUri)
    {
        // var_dump($this->allRoutes);
        // die();
        foreach ($this->allRoutes as $route) {
            if ($route['path'] === $requestUri) {
                // Temukan handler yang sesuai
                // $handlerClass = "Modules\\".$route['handler'];
                // $method = $route['method'];

            $handlerInfo = explode('::', $route['handler']);
            $handlerClass = 'Modules\\' . $handlerInfo[0];
            $handlerMethod = $handlerInfo[1];

                // var_dump($handlerClass);
                // die();

                if (class_exists($handlerClass)) {
                    $handler = new $handlerClass();
                    return $handler->$handlerMethod();
                }
            }
        }

        // Jika tidak ada rute yang cocok
        return ['error' => 'route not found'];
    }
}
