<?php

namespace App\Http;

use App\Core\ServiceContainer;
use App\Core\LogManager;
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

    public function __construct($configFile, ServiceContainer $serviceContainer)
    {
        $this->routes = Yaml::parseFile($configFile);
        $this->serviceContainer = $serviceContainer; // Simpan ServiceContainer

        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();

        if (isset($this->routes['imports'])) {
            foreach ($this->routes['imports'] as $import) {
                $importedRoutes = Yaml::parseFile($import['resource']);
                $this->allRoutes = array_merge($this->allRoutes, $importedRoutes['routes']);
            }
        }

        $globalMiddleware = [
            \App\Http\Middlewares\CorsMiddleware::class,
            \App\Http\Middlewares\FixHeadersMiddleware::class,
            InputSanitizationMiddleware::class // Menambahkan Input Sanitization Middleware
        ];

        $this->allRoutes = $this->addGlobalMiddleware($this->allRoutes, $globalMiddleware);
        // dd($this->allRoutes);
    }

    public function dispatch(Request $request): Response
    {
        $requestId = uniqid('request_', true);
        $requestUri = $request->getPathInfo();
        $requestMethod = $request->getMethod();

        // Catat permintaan yang diterima
        $this->logger->info('Dispatching request', [
            'requestId' => $requestId,
            'uri' => $requestUri,
            'method' => $requestMethod,
        ]);

        try {
            foreach ($this->allRoutes as $route) {
                $routePath = preg_replace('/\{[a-zA-Z]+\}/', '([a-zA-Z0-9]+)', $route['path']);

                if (preg_match('#^' . $routePath . '$#', $requestUri, $matches) && in_array($requestMethod, $route['methods'])) {
                    array_shift($matches); // Menghapus full match
                    $handlerInfo = explode('::', $route['handler']);
                    $handlerClass = $handlerInfo[0];
                    $handlerMethod = $handlerInfo[1];

                    // **Cek apakah class handler ada**
                    if (!class_exists($handlerClass)) {
                        return $this->createErrorResponse("Handler class '{$handlerClass}' not found", 404);
                    }

                    // $expHandlerClass = explode("\\", $handlerClass);

                    // $handlerSc = end($expHandlerClass);

                    //                     // Mendapatkan instance handler dari ServiceContainer
                    // $handler = $this->serviceContainer->get($handlerSc); // Memanggil handler dengan format yang tepat

                    // if(!$handler) {
                        $handler = new $handlerClass($this->serviceContainer);
                    // }


                    // **Cek apakah method handler ada di dalam class**
                    if (!method_exists($handler, $handlerMethod)) {
                        return $this->createErrorResponse("Method '{$handlerMethod}' not found in class '{$handlerClass}'", 404);
                    }

                    // Eksekusi middleware jika ada
                    $middlewares = isset($route['middleware']) ? $route['middleware'] : [];
                    $response = $this->runMiddlewares($middlewares, $request, function ($request) use ($handler, $handlerMethod, $matches) {
                        return new Response(
                            json_encode($handler->$handlerMethod(...$matches)),
                            200,
                            ['Content-Type' => 'application/json']
                        );
                    });

                    // Pastikan response adalah objek Response
                    if ($response instanceof Response) {
                        return $response;
                    }

                    // Jika bukan response, kembalikan sebagai error
                    return $this->createErrorResponse("Unexpected response type", 500);
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

        if(in_array($middlewareName, ["auth"])) {
            $middlewareClass = "App\\Http\\Middlewares\\" . ucfirst($middlewareName) . "Middleware";
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
