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
    private $mapMiddleware = [
        "auth" => AuthMiddleware::class
    ];
    private $globalMiddleware = [
        // \App\Http\Middlewares\CorsMiddleware::class,
        // \App\Http\Middlewares\FixHeadersMiddleware::class,
        // InputSanitizationMiddleware::class // Middleware sanitasi input
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

        // Muat rute tambahan yang diimpor dari file lain
        if (isset($this->routes['imports'])) {
            foreach ($this->routes['imports'] as $import) {
                $importedRoutes = Yaml::parseFile($import['resource']);
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
    //  * @return Response
     */
    public function dispatch(Request $request)
    {
        $requestId = uniqid('request_', true);
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        // Logging request yang diterima
        $this->logger->info('Dispatching request', [
            'requestId' => $requestId,
            'uri' => $path,
            'method' => $method,
        ]);

        try {
            foreach ($this->allRoutes as $route) {
                // Cek apakah rute cocok dengan URI dan metode request
                $routePath = preg_replace('/\{[a-zA-Z]+\}/', '([a-zA-Z0-9]+)', $route['path']);
                if (preg_match('#^' . $routePath . '$#', $path) && in_array($method, $route['methods'])) {
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
                        $result = call_user_func_array([$handler, $handlerMethod], $args);

                        // Otomatis mengubah array atau object menjadi JSON
                        if (is_array($result) || is_object($result)) {
                            return response_json($result); // Gunakan helper untuk JSON dengan status 200
                        }

                        // Kembalikan hasil langsung jika bukan array/object
                        return $result;
                    });


                    return $response;
                }
            }

            return $this->createErrorResponse('route not found', 404);
        } catch (\Throwable $exception) {
            // Logging kesalahan yang terjadi
            $this->logger->error('An error occurred', [
                'message' => $exception->getMessage(),
                'stack' => $exception->getTraceAsString(),
            ]);

            // Menangani exception dan mengembalikan response yang sesuai
            $exceptionHandler = new \App\Core\ExceptionHandler();
            return $exceptionHandler->handle($exception);
        }
    }

    /**
     * Menjalankan middleware satu per satu
     * @param array $middlewares
     * @param Request $request
     * @param callable $next
    //  * @return Response
     */
    private function runMiddlewares(array $middlewares, Request $request, callable $next)
    {
        if (empty($middlewares)) {
            return $next($request);
        }

        $middlewareName = array_shift($middlewares);
        $middlewareClass = $this->mapMiddleware[$middlewareName] ?? "App\\Http\\Middlewares\\{$middlewareName}";

        // if (!class_exists($middlewareClass)) {
        //     return new Response(
        //         json_encode(['error' => "Middleware class '{$middlewareClass}' not found"]),
        //         404,
        //         ['Content-Type' => 'application/json']
        //     );
        // }

        if (!class_exists($middlewareClass)) {
            // Kembalikan error dalam bentuk array dan serahkan ke dispatcher untuk diolah menjadi JSON
            return ['error' => "Middleware class '{$middlewareClass}' not found"];
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
    //  * @return Response
     */
    private function createErrorResponse(string $message, int $statusCode)
    {
        return [
            'error' => $message,
            'status_code' => $statusCode
        ];
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
