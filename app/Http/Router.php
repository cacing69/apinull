<?php

namespace App\Http;

use App\Core\LogManager;
use App\Http\Middlewares\AuthMiddleware;
use App\Http\Middlewares\InputSanitizationMiddleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class Router
{
    private $routes;
    private $allRoutes = [];
    private $logger;
    private $serviceContainer; // Tambahkan property untuk ServiceContainer

    private $mapMiddleware = [
            "auth" => AuthMiddleware::class
        ];
    private $globalMiddleware = [
        \App\Http\Middlewares\CorsMiddleware::class,
        \App\Http\Middlewares\FixHeadersMiddleware::class,
        InputSanitizationMiddleware::class // Menambahkan Input Sanitization Middleware
    ];
    // public function __construct($configFile, ServiceContainer $serviceContainer)
    public function __construct($configFile)
    {
        $this->routes = Yaml::parseFile($configFile);
        // $this->serviceContainer = $serviceContainer; // Simpan ServiceContainer

        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();

        if (isset($this->routes['imports'])) {
            foreach ($this->routes['imports'] as $import) {
                $importedRoutes = Yaml::parseFile($import['resource']);
                $this->allRoutes = array_merge($this->allRoutes, $importedRoutes['routes']);
            }
        }


        $this->allRoutes = $this->addGlobalMiddleware($this->allRoutes, $this->globalMiddleware);
        // dd($this->allRoutes);
    }

    protected function getParamsFromPath($routePath, $actualPath) {
        // Contoh sederhana untuk mengekstrak parameter seperti /check/{id}
        $routeParts = explode('/', trim($routePath, '/'));
        $actualParts = explode('/', trim($actualPath, '/'));
        $params = [];
        foreach ($routeParts as $index => $part) { if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) { $paramName = trim($part, '{}'); $params[$paramName] = $actualParts[$index] ?? null; } } return $params;
    }

    // protected function handleNotFound() { http_response_code(404); echo "404 Not Found"; }

    // protected function loadRoutes($routeFile) { $this->routes = Yaml::parseFile($routeFile)['routes']; }

    public function dispatch(Request $request): Response
    {
        $requestId = uniqid('request_', true);
        // $requestUri = $request->getPathInfo();
        $path = $request->getPathInfo();
        $method = $request->getMethod();
        // $requestMethod = $request->getMethod();

        // Catat permintaan yang diterima
        $this->logger->info('Dispatching request', [
            'requestId' => $requestId,
            'uri' => $path,
            'method' => $method,
        ]);

        try {
            foreach ($this->allRoutes as $route) {
                $routePath = preg_replace('/\{[a-zA-Z]+\}/', '([a-zA-Z0-9]+)', $route['path']);

                if (preg_match('#^' . $routePath . '$#', $path, $matches) && in_array($method, $route['methods'])) {
                    // dd($routePath, $route['path'], $path);
                    if(in_array($method, $route["methods"])){
                    // array_shift($matches); // Menghapus full match
                    // $handlerInfo = explode('::', $route['handler']);
                    // $handlerClass = $handlerInfo[0];
                    // $handlerMethod = $handlerInfo[1];


                    [$handlerClass, $handlerMethod] = explode('::', $route['handler']);
                    $handler = new $handlerClass();

                    $params = $this->getParamsFromPath($route['path'], $path);

                    $reflectionMethod = new \ReflectionMethod($handlerClass, $handlerMethod);
                    $methodParams = $reflectionMethod->getParameters();

                    $args = [];



                    // foreach ($methodParams as $param) {
                    //     if($param->getClass() && $param->getClass()->name === Request::class) {
                    //         $args[] = $request;
                    //     } elseif (isset($params[$param->name])) {
                    //         $args[] = $params[$param->name];
                    //     } else {
                    //         $args[] = null;
                    //     }
                    // }

                    foreach ($methodParams as $param) {
                        $paramType = $param->getType();

                        // Cek apakah tipe parameter adalah sebuah class (non-builtin)
                        if ($paramType instanceof \ReflectionNamedType && !$paramType->isBuiltin()) {
                            $paramClass = new \ReflectionClass($paramType->getName());

                            // Jika parameternya adalah Request
                            if ($paramClass->getName() === Request::class) {
                                $args[] = $request;
                            }
                            // Cek apakah parameter ada di $params (yang diekstrak dari route)
                            elseif (isset($params[$param->getName()])) {
                                $args[] = $params[$param->getName()];
                            } else {
                                // Jika parameter class, tetapi tidak ada di route atau request, set null
                                $args[] = null;
                            }
                        } else {
                            // Untuk tipe built-in atau yang tidak diketahui, cek apakah ada di $params
                            if (isset($params[$param->getName()])) {
                                $args[] = $params[$param->getName()];
                            } else {
                                // Set default value jika ada, atau null jika tidak ada
                                $args[] = $param->isOptional() ? $param->getDefaultValue() : null;
                            }
                        }
                    }


                    // **Cek apakah class handler ada**
                    // if (!class_exists($handlerClass)) {
                    //     return $this->createErrorResponse("Handler class '{$handlerClass}' not found", 404);
                    // }

                    // $expHandlerClass = explode("\\", $handlerClass);

                    // $handlerSc = end($expHandlerClass);

                    //                     // Mendapatkan instance handler dari ServiceContainer
                    // $handler = $this->serviceContainer->get($handlerSc); // Memanggil handler dengan format yang tepat

                    // if(!$handler) {
                        // $handler = new $handlerClass($this->serviceContainer);
                    // }


                    // **Cek apakah method handler ada di dalam class**
                    // if (!method_exists($handler, $handlerMethod)) {
                    //     return $this->createErrorResponse("Method '{$handlerMethod}' not found in class '{$handlerClass}'", 404);
                    // }

                    // Eksekusi middleware jika ada
                    $middlewares = isset($route['middleware']) ? $route['middleware'] : [];

                    $response = $this->runMiddlewares($middlewares, $request, function ($request) use ($handler, $handlerMethod, $args) {
                        return new Response(
                            // json_encode($handler->$handlerMethod(...$matches)),
                            json_encode(call_user_func_array([$handler, $handlerMethod], $args)),
                            200,
                            ['Content-Type' => 'application/json']
                        );
                        // return call_user_func_array([$handler, $handlerMethod], $args);
                    });

                    // Pastikan response adalah objek Response
                    if ($response instanceof Response) {
                        return $response;
                    }

                    // Jika bukan response, kembalikan sebagai error
                    return $this->createErrorResponse("Unexpected response type", 500);
                }
            }
            }

            // Jika tidak ada rute yang cocok
            return $this->createErrorResponse('Route not found', 404);
        } catch (\Throwable $exception) {
            // Catat kesalahan yang terjadi
            $this->logger->error('An error occurred', [
                'message' => $exception->getMessage(),
                'stack' => $exception->getTraceAsString(),
            ]);

            // Menangkap exception dan mengalihkan ke Exception Handler
            $exceptionHandler = new \App\Core\ExceptionHandler();
            return $exceptionHandler->handle($exception);
        }
    }

    private function runMiddlewares(array $middlewares, Request $request, callable $next): Response
    {
        if (empty($middlewares)) {
            return $next($request); // Memastikan $next di sini memanggil handler dengan Request
        }

        $middlewareName = array_shift($middlewares);

        if(in_array($middlewareName, array_keys($this->mapMiddleware))) {
            // $middlewareClass = "App\\Http\\Middlewares\\" . ucfirst($middlewareName) . "Middleware";
            $middlewareClass = $this->mapMiddleware[$middlewareName];
        } else {
            $middlewareClass = "App\\Http\\Middlewares\\" .$middlewareName;
        }

        if (!class_exists($middlewareClass)) {
            return new Response(
                json_encode(['error' => "Middleware class '{$middlewareClass}' not found"]),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $middleware = new $middlewareClass();

        // Memanggil middleware dengan Request
        return $middleware->handle($request, function ($request) use ($middlewares, $next) {
            return $this->runMiddlewares($middlewares, $request, $next);
        });
    }


    private function createErrorResponse(string $message, int $statusCode): Response
    {
        return new Response(
            json_encode(['error' => $message]),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    private function addGlobalMiddleware(array $routes, array $middlewareClass): array
    {
        foreach ($routes as &$route) {
            if (!isset($route['middleware'])) {
                $route['middleware'] = [];
            }
            foreach ($middlewareClass as $middleware) {
                $route['middleware'][] = basename(str_replace('\\', '/', $middleware)); // Menambahkan CORS middleware
            }
        }

        return $routes;
    }
}
