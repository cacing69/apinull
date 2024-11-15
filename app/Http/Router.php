<?php

namespace App\Http;

use App\Http\Middlewares\CorsMiddleware;
use App\Http\Middlewares\FixHeadersMiddleware;
use App\Http\Middlewares\InputSanitizationMiddleware;
use App\Http\Middlewares\AuthMiddleware;
use App\Kernel\Container;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;
use ReflectionMethod;
use ReflectionClass;
use Throwable;

class Router
{
    /**
     * Daftar rute yang terdaftar
     *
     * @var array
     */
    private array $routes;

    /**
     * Kontainer untuk mengelola dependensi
     *
     * @var Container
     */
    private Container $container;

    /**
     * Semua rute yang telah diimpor dan digabungkan
     *
     * @var array
     */
    private array $allRoutes = [];
    private array $routeIndex = [];

    /**
     * Daftar rute yang memiliki path duplikat
     *
     * @var array
     */
    private array $duplicates = [];

    /**
     * Pemetaan nama middleware ke kelas middleware
     *
     * @var array
     */
    private array $mapMiddleware = [
        "auth" => AuthMiddleware::class,
    ];

    /**
     * Daftar middleware global yang diterapkan ke semua rute
     *
     * @var array
     */
    private array $globalMiddleware = [
        CorsMiddleware::class,
        FixHeadersMiddleware::class,
        InputSanitizationMiddleware::class,
    ];


    /**
     * Konstruktor untuk inisialisasi router dengan file konfigurasi dan kontainer
     *
     * @param string $configFile
     * @param Container $container
     */
    public function __construct(string $configFile, Container $container)
    {

        $this->routes = Yaml::parseFile($configFile);
        $this->container = $container;


        $this->initializeRoutes();
    }

    /**
     * Menginisialisasi dan memproses rute yang diimpor dari file YAML
     */
    private function initializeRoutes(): void
    {
        if (isset($this->routes['imports'])) {
            foreach ($this->routes['imports'] as $import) {
                $this->importRoutes($import);
            }
        }

        // Gabungkan rute yang diambil dari atribut dengan rute yang ada
        $attributeRoutes = $this->getRoutesFromAttributes();
        $this->allRoutes = array_merge($this->allRoutes, $attributeRoutes);

        // Buat indeks untuk pencarian langsung berdasarkan path
        $this->routeIndex = $this->buildRouteIndex($this->allRoutes);

        $this->allRoutes = $this->addGlobalMiddleware($this->allRoutes, $this->globalMiddleware);
    }

    /**
     * Membuat indeks untuk pencarian rute berdasarkan path
     */
    private function buildRouteIndex(array $routes): array
    {
        $staticRoutes = [];
        $dynamicRoutes = [];

        foreach ($routes as $route) {
            // Jika path mengandung parameter dinamis, tambahkan ke dynamicRoutes
            if (strpos($route['path'], '{') !== false) {
                $dynamicRoutes[] = $route;
            } else {
                // Rute statis bisa langsung diindeks
                $staticRoutes[$route['path']] = $route;
            }
        }

        return ['static' => $staticRoutes, 'dynamic' => $dynamicRoutes];
    }

    /**
     * Mengimpor rute dari file eksternal dan menangani duplikat path
     *
     * @param array $import
     */
    private function importRoutes(array $import): void
    {
        if (file_exists(app_path($import['resource']))) {
            $importedRoutes = Yaml::parseFile(app_path($import['resource']));

            $this->handleRouteGroups($importedRoutes);
            $this->checkDuplicatePaths($importedRoutes);

            $this->allRoutes = array_merge($this->allRoutes, $importedRoutes['routes']);
        }
    }

    /**
     * Menangani rute grup jika ada dalam file konfigurasi
     *
     * @param array $importedRoutes
     */
    private function handleRouteGroups(array &$importedRoutes): void
    {
        if (isset($importedRoutes['group']) && strlen($importedRoutes['group']) > 0) {
            array_walk($importedRoutes['routes'], function (&$route) use ($importedRoutes) {
                $route['path'] = '/' . $importedRoutes['group'] . $route['path'];
            });
        }
    }

    /**
     * Memeriksa apakah ada path duplikat di rute yang diimpor
     *
     * @param array $importedRoutes
     */
    private function checkDuplicatePaths(array $importedRoutes): void
    {
        foreach ($importedRoutes['routes'] as $key => $value) {
            $routeIdentifier = $value['path'] . '|' . implode(',', $value['methods']);

            if (isset($this->duplicates[$routeIdentifier])) {
                $this->duplicates[$routeIdentifier] += 1;
            } else {
                $this->duplicates[$routeIdentifier] = 1;
            }
        }
    }

    /**
     * Menambahkan middleware global ke semua rute
     *
     * @param array $routes
     * @param array $middlewareClass
     * @return array
     */
    private function addGlobalMiddleware(array $routes, array $middlewareClass): array
    {
        $mappedMiddleware = array_map(fn($middleware) => basename(str_replace('\\', '/', $middleware)), $middlewareClass);

        return array_map(function ($route) use ($mappedMiddleware) {
            $route['middleware'] = array_merge(
                $route['middleware'] ?? [],
                $mappedMiddleware
            );

            return $route;
        }, $routes);
    }


    /**
     * Mengekstrak parameter dari path, seperti /check/{id}
     *
     * @param string $routePath
     * @param string $actualPath
     * @return array
     */
    protected function getParamsFromPath(string $routePath, string $actualPath): array
    {
        // Memecah path berdasarkan '/'
        $routeParts = explode('/', $routePath);
        $actualParts = explode('/', $actualPath);

        // Menghapus bagian pertama dan terakhir jika kosong (path bisa dimulai atau diakhiri dengan '/')
        $routeParts = array_filter($routeParts, fn($part) => $part !== '');
        $actualParts = array_filter($actualParts, fn($part) => $part !== '');

        // Menyusun parameter berdasarkan pola '{param}'
        $params = [];
        foreach ($routeParts as $index => $part) {
            // Mengecek apakah bagian path adalah parameter dengan format {param}
            if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $actualParts[$index] ?? null;
            }
        }

        return $params;
    }


    /**
     * Menangani dan mendispatch request ke handler yang sesuai
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dispatch(Request $request)
    {
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        // Cek rute statis terlebih dahulu
        if (isset($this->routeIndex['static'][$path])) {
            $route = $this->routeIndex['static'][$path];

            // Periksa metode HTTP
            if (!in_array($method, $route['methods'])) {
                return $this->createErrorResponse("Method {$method} not allowed for this route", 405);
            }

            return $this->handleRoute($route, $request);
        }

        // Jika tidak ditemukan, cari rute dinamis dengan regex
        foreach ($this->routeIndex['dynamic'] as $route) {
            $routePathPattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([a-zA-Z0-9_-]+)', $route['path']);
            if (preg_match('#^' . $routePathPattern . '$#', $path, $matches)) {
                // Ekstrak parameter dari URL yang cocok
                $params = $this->getParamsFromPath($route['path'], $path);

                // Periksa metode HTTP
                if (!in_array($method, $route['methods'])) {
                    return $this->createErrorResponse("Method {$method} not allowed for this route", 405);
                }

                return $this->handleRoute($route, $request);
            }
        }

        return $this->createErrorResponse('Route not found', 404);
    }


    /**
     * Menangani eksekusi route, termasuk middleware dan handler
     *
     * @param array $route
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleRoute(array $route, Request $request)
    {
        [$handlerClass, $handlerMethod] = explode('::', $route['handler']);
        $handler = $this->container->make($handlerClass);
        $params = $this->getParamsFromPath($route['path'], $request->getPathInfo());

        $reflectionMethod = new ReflectionMethod($handlerClass, $handlerMethod);
        $methodParams = $reflectionMethod->getParameters();
        $args = $this->resolveMethodArgs($methodParams, $params, $request);

        $middlewares = $route['middleware'] ?? [];
        $response = $this->runMiddlewares($middlewares, $request, function () use ($handler, $handlerMethod, $args) {
            return call_user_func_array([$handler, $handlerMethod], $args);
        });

        return response()->json($response);
    }

    /**
     * Menjalankan middleware untuk rute tertentu
     *
     * @param array $middlewares
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    private function runMiddlewares(array $middlewares, Request $request, callable $next)
    {
        if (empty($middlewares)) {
            return $next($request);
        }

        $middlewareName = array_shift($middlewares);
        $middlewareClass = $this->mapMiddleware[$middlewareName] ?? "App\\Http\\Middlewares\\{$middlewareName}";

        if (!class_exists($middlewareClass)) {
            return response()->json(['error' => "Middleware class '{$middlewareClass}' not found"], 404);
        }

        $middleware = new $middlewareClass();
        return $middleware->handle($request, fn($request) => $this->runMiddlewares($middlewares, $request, $next));
    }

    /**
     * Membuat response error dalam format JSON
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    private function createErrorResponse(string $message, int $statusCode)
    {
        return response()->json([
            'data' => null,
            'meta' => null,
            'error' => ['message' => $message]
        ], $statusCode);
    }

    /**
     * Menangani exception yang terjadi selama proses dispatch
     *
     * @param Throwable $exception
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleException(Throwable $exception)
    {
        $exceptionHandler = new \App\Kernel\ExceptionHandler();
        return $exceptionHandler->handle($exception);
    }

    /**
     * Menyelesaikan argument method handler berdasarkan parameter dan request
     *
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
                $paramClass = new ReflectionClass($paramType->getName());

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

    // Menambahkan fungsi untuk membaca atribut
    private function getRoutesFromAttributes(): array
    {
        $routes = [];
        $handlerDirectory = app_path('src'.DIRECTORY_SEPARATOR.'Modules'); // Sesuaikan dengan lokasi modul

        // Menemukan semua kelas PHP dalam direktori tertentu
        foreach (glob($handlerDirectory . '/*/Http/*.php') as $file) {
            $className = $this->getClassNameFromFile($file);

            if (class_exists($className)) {
                $reflectionClass = new ReflectionClass($className);

                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    $attributes = $reflectionMethod->getAttributes(Route::class); // Mengambil atribut Route

                    foreach ($attributes as $attribute) {
                        /** @var Route $routeAttribute */
                        $routeAttribute = $attribute->newInstance(); // Mendapatkan instance dari atribut Route

                        // Tambahkan route ke dalam daftar routes
                        $routes[] = [
                            'path' => $routeAttribute->path,
                            'methods' => $routeAttribute->methods,
                            'middleware' => $routeAttribute->middleware,
                            'handler' => $className . '::' . $reflectionMethod->getName(),
                        ];
                    }
                }
            }
        }

        return $routes;
    }

// Fungsi untuk mendapatkan nama kelas dari path file PHP
private function getClassNameFromFile(string $file): string
{
    $namespace = $this->getNamespaceFromFile($file);
    $className = basename($file, '.php');

    return $namespace . '\\' . $className;
}

// Fungsi untuk mendapatkan namespace dari file PHP
private function getNamespaceFromFile(string $file): string
{
    $fileContents = file_get_contents($file);
    if (preg_match('/namespace\s+(.+);/', $fileContents, $matches)) {
        return $matches[1];
    }

    return '';
}
}
