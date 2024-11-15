<?php

namespace App\Http;

use App\Http\Middlewares\CorsMiddleware;
use App\Http\Middlewares\FixHeadersMiddleware;
use App\Http\Middlewares\InputSanitizationMiddleware;
use App\Kernel\LogManager;
use App\Http\Middlewares\AuthMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

class Router
{
    private $routes;
    private $allRoutes = [];
    private $logger;
    private $pathTracker = [];
    private $duplicates = [];
    private $mapMiddleware = [
        "auth" => AuthMiddleware::class
    ];
    private $globalMiddleware = [
        CorsMiddleware::class,
        FixHeadersMiddleware::class,
        InputSanitizationMiddleware::class
    ];

    /**
     * Constructor untuk inisialisasi router dengan file YAML dan logger
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        $this->routes = Yaml::parseFile($configFile);

        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();

        // Start with an empty array to hold paths and duplicates

        // Muat rute tambahan yang diimpor dari file lain
        if (isset($this->routes['imports'])) {
            foreach ($this->routes['imports'] as $import) {

                if(file_exists(app_path($import['resource']))){
                    $importedRoutes = Yaml::parseFile(app_path($import['resource']));

                    if(array_key_exists("group", $importedRoutes)){
                        if(strlen($importedRoutes["group"]) > 0) {
                            foreach ($importedRoutes["routes"] as $key => $value) {
                                $importedRoutes["routes"][$key]["path"] = "/" . $importedRoutes["group"] . $value["path"];
                            }
                        }
                    }

                    foreach ($importedRoutes["routes"] as $key => $value) {
                        // Memeriksa path dengan metode yang unik
                        $routeIdentifier = $importedRoutes["routes"][$key]["path"] . "|" . implode(',', $value['methods']);

                        if (isset($this->pathTracker[$routeIdentifier])) {
                            $this->duplicates[] = $importedRoutes["routes"][$key]["path"];
                        } else {
                            $this->pathTracker[$routeIdentifier] = true;
                        }
                    }
                }

                $this->allRoutes = array_merge($this->allRoutes, $importedRoutes['routes']);

            }
        }

        // Tambahkan middleware global ke semua rute
        $this->allRoutes = $this->addGlobalMiddleware($this->allRoutes, $this->globalMiddleware);
    }

    /**
     * Mengekstrak parameter dari path seperti /check/{id}
     * @param string $routePath
     * @param string $actualPath
     * @return array
     */
    protected function getParamsFromPath($routePath, $actualPath)
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $actualParts = explode('/', trim($actualPath, '/'));
        $params = [];

        foreach ($routeParts as $index => $part) {
            if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $actualParts[$index] ?? null;
            }
        }

        return $params;
    }

    /**
     * Mendapatkan dan mendispatch request sesuai rute yang terdaftar
     * @param Request $request
     */
    public function dispatch(Request $request)
    {
        $path = $request->getPathInfo();

        if (!empty($this->duplicates) && in_array($path, $this->duplicates)) {
            // Handle or log the duplicates
            // For example: log the duplicates or return an error response
            return $this->createErrorResponse("path '".$path."' duplicate", 500);
        }

        // $requestId = uniqid('request_', true);

        $method = $request->getMethod();

        // Logging request yang diterima
        // $this->logger->info('Dispatching request', [
        //     'requestId' => $requestId,
        //     'uri' => $path,
        //     'method' => $method,
        // ]);

        try {
            foreach ($this->allRoutes as $route) {
                // Cek apakah rute cocok dengan URI dan metode request
                $routePath = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([a-zA-Z0-9_-]+)', $route['path']);

                if (preg_match('#^' . $routePath . '$#', $path)) {

                    if (!in_array($method, $route['methods'])) {
                        return $this->createErrorResponse("method {$method} not allowed for this route", 405);
                    }

                    [$handlerClass, $handlerMethod] = explode('::', $route['handler']);
                    $handler = new $handlerClass();

                    $params = $this->getParamsFromPath($route['path'], $path);

                    // Reflection untuk mendapatkan parameter dari handler method
                    $reflectionMethod = new \ReflectionMethod($handlerClass, $handlerMethod);
                    $methodParams = $reflectionMethod->getParameters();

                    $args = $this->resolveMethodArgs($methodParams, $params, $request);



                    // Eksekusi middleware dan handler
                    $middlewares = $route['middleware'] ?? [];
                    $response = $this->runMiddlewares($middlewares, $request, function ($request) use ($handler, $handlerMethod, $args) {
                        // Dapatkan hasil dari handler
                        return call_user_func_array([$handler, $handlerMethod], $args);
                    });

                    // Pastikan respons yang dikembalikan di sini adalah objek Response dari Laravel
                    return response()->json($response); // Mengubah hasil menjadi JSON di sini
                }
            }

            return $this->createErrorResponse('route not found', 404);
        } catch (\Throwable $exception) {
            // Logging kesalahan yang terjadi
            // $this->logger->error('An error occurred', [
            //     'message' => $exception->getMessage(),
            //     'stack' => $exception->getTraceAsString(),
            // ]);

            // Menangani exception dan mengembalikan response yang sesuai
            $exceptionHandler = new \App\Kernel\ExceptionHandler();
            return $exceptionHandler->handle($exception);
        }
    }

    /**
     * Menjalankan middleware satu per satu
     * @param array $middlewares
     * @param Request $request
     * @param callable $next
     */
    private function runMiddlewares(array $middlewares, Request $request, callable $next)
    {
        if (empty($middlewares)) {
            return $next($request);
        }

        // dd($this);

        $middlewareName = array_shift($middlewares);
        $middlewareClass = $this->mapMiddleware[$middlewareName] ?? "App\\Http\\Middlewares\\{$middlewareName}";

        if (!class_exists($middlewareClass)) {
            // Jika middleware tidak ditemukan, kirimkan error dengan JSON response
            return response()->json(['error' => "Middleware class '{$middlewareClass}' not found"], 404);
        }

        $middleware = new $middlewareClass();

        return $middleware->handle($request, function ($request) use ($middlewares, $next) {
            return $this->runMiddlewares($middlewares, $request, $next);
        });
    }

    /**
     * Membuat response error
     * @param string $message
     * @param int $statusCode
     */
    private function createErrorResponse(string $message, int $statusCode)
    {
        return response()->json([
            "data" => null,
            "meta" => null,
            'error' => [
                "message" => $message
            ]
        ], $statusCode);
    }

    /**
     * Menambahkan middleware global ke semua rute
     * @param array $routes
     * @param array $middlewareClass
     * @return array
     */
    private function addGlobalMiddleware(array $routes, array $middlewareClass): array
    {
        foreach ($routes as &$route) {
            $route['middleware'] = array_merge($route['middleware'] ?? [], array_map(function ($middleware) {
                return basename(str_replace('\\', '/', $middleware));
            }, $middlewareClass));
        }

        return $routes;
    }

    /**
     * Resolusi argument untuk handler method berdasarkan parameter dan request
     * @param array $methodParams
     * @param array $params
     * @param Request $request
     * @return array
     */
    private function resolveMethodArgs(array $methodParams, array $params, Request $request): array
    {
        $args = [];

        foreach ($methodParams as $param) {
            $paramType = $param->getType();

            if ($paramType instanceof \ReflectionNamedType && !$paramType->isBuiltin()) {
                $paramClass = new \ReflectionClass($paramType->getName());

                if ($paramClass->getName() === Request::class) {
                    $args[] = $request;
                } elseif (isset($params[$param->getName()])) {
                    $args[] = $params[$param->getName()];
                } else {
                    $args[] = null;
                }
            } else {
                $args[] = $params[$param->getName()] ?? ($param->isOptional() ? $param->getDefaultValue() : null);
            }
        }

        return $args;
    }
}
