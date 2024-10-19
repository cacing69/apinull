<?php

namespace App\Http;

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
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        // var_dump($this->allRoutes);
        // die();
        foreach ($this->allRoutes as $route) {
            // $routePath = preg_replace('/\{[a-zA-Z]+\}/', '([a-zA-Z0-9]+)', $route['path']);
             // Buat regex untuk mencocokkan URL dengan parameter
            // Misalnya: /user/{userId}/order/{orderId} menjadi /user/([a-zA-Z0-9]+)/order/([a-zA-Z0-9]+)
            $routePath = preg_replace('/\{[a-zA-Z]+\}/', '([a-zA-Z0-9]+)', $route['path']);

            //  if ($route['path'] === $requestUri && in_array($requestMethod, $route['methods'])) {
            if (preg_match('#^' . $routePath . '$#', $requestUri, $matches) && in_array($requestMethod, $route['methods'])) {
                array_shift($matches); // Menghapus full match
                // Temukan handler yang sesuai
                // $handlerClass = "Modules\\".$route['handler'];
                // $method = $route['method'];

                $handlerInfo = explode('::', $route['handler']);
                // $handlerClass = 'Modules\\' . $handlerInfo[0];
                $handlerClass = $handlerInfo[0];
                $handlerMethod = $handlerInfo[1];

                // **Cek apakah class handler ada**
                if (!class_exists($handlerClass)) {
                    return ['error' => "handler class '{$handlerClass}' not found"];
                }

                $handler = new $handlerClass();

                // **Cek apakah method handler ada di dalam class**
                if (!method_exists($handler, $handlerMethod)) {
                    return ['error' => "method '{$handlerMethod}' not found in class '{$handlerClass}'"];
                }

                // var_dump($handlerClass);
                // die();

                // if (class_exists($handlerClass)) {

                    return $handler->$handlerMethod(...$matches); // Kirim parameter ke handler
                // }
            }
        }

        // Jika tidak ada rute yang cocok
        return ['error' => 'route not found'];
    }
}
