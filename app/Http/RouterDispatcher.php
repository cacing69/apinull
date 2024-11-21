<?php

namespace App\Http;

use App\Http\Middlewares\{
    CorsMiddleware,
    FixHeadersMiddleware,
    InputSanitizationMiddleware
};
use App\Kernel\Container;
use Illuminate\Http\Request;
use ReflectionMethod;
use ReflectionClass;
use Throwable;

class RouterDispatcher
{
    /** @var array Registered routes */
    private array $routes;

    /** @var Container Dependency container */
    private Container $container;

    /** @var array Combined imported routes */
    private array $allRoutes = [];
    private array $dynamicRoutes = [];

    /** @var array Routes with duplicate paths */
    private array $duplicates = [];

    /** @var array Global middleware applied to all routes */
    private const GLOBAL_MIDDLEWARE = [
        CorsMiddleware::class,
        FixHeadersMiddleware::class,
        InputSanitizationMiddleware::class,
    ];

    private array $methodArgsCache = [];

    // Menyimpan rute statis dengan hash
    private array $staticRoutes = [];

    private array $paramCache = [];

    private MiddlewareExecutor $middlewareExecutor;

    public function __construct(string $configFile, Container $container)
    {
        $this->container = $container;
        $this->middlewareExecutor = new MiddlewareExecutor();

        // $this->routes = Yaml::parseFile($configFile);
        $this->initializeRoutes();
    }

    private function initializeRoutes(): void
    {
        $this->importConfiguredRoutes();
        $this->mergeAttributeRoutes();
        $this->buildRouteIndexes();
        $this->applyGlobalMiddleware();
    }

    private function importConfiguredRoutes(): void
    {
        // $this->routes = glob(app_path("src/Modules/*/*.routes.yaml"));
        $this->routes = glob(app_path("src/Modules/*/*.routes.php"));

        foreach ($this->routes as $import) {
            $this->processRouteImport($import);
        }
    }

    private function processRouteImport(string $import): void
    {
        // $importedRoutes = Yaml::parseFile($import);
        $importedRoutes = include $import;

        $this->processRouteGroups($importedRoutes);
        $this->processRouteMiddlewares($importedRoutes);
        $this->trackDuplicateRoutes($importedRoutes);

        $this->allRoutes = array_merge(
            $this->allRoutes,
            // $importedRoutes['routes']
            $importedRoutes['routes']
        );
    }

    private function processRouteMiddlewares(&$importedRoutes)
    {
        if (empty($importedRoutes['middleware'])) {
            return;
        }

        $middleware = $importedRoutes["middleware"];


        array_walk($importedRoutes['routes'], function (&$route) use ($middleware) {
            // dd($route);
            if(array_key_exists("middleware", $route)){
                $route['middleware'] = array_merge($route['middleware'], $middleware);
            } else {
                $route['middleware'] = $middleware;
            }
        });
    }

    private function processRouteGroups(array &$routes): void
    {
        if (empty($routes['group'])) {
            return;
        }

        array_walk($routes['routes'], function (&$route) use ($routes) {
            $route['path'] = '/' . $routes['group'] . $route['path'];
        });
    }

    private function trackDuplicateRoutes(array $routes): void
    {
        foreach ($routes['routes'] as $route) {
            $routeKey = $this->createRouteKey($route);
            $this->duplicates[$routeKey] = ($this->duplicates[$routeKey] ?? 0) + 1;
        }
    }

    private function createRouteKey(array $route): string
    {
        return $route['path'] . '|' . implode(',', $route['methods']);
    }

    private function mergeAttributeRoutes(): void
    {
        $attributeRoutes = $this->scanAttributeRoutes();
        $this->allRoutes = array_merge($this->allRoutes, $attributeRoutes);
    }

    private function buildRouteIndexes(): void
    {
        foreach ($this->allRoutes as $route) {
            if ($this->isStaticRoute($route)) {
                $this->indexStaticRoute($route);
            } else {
                $this->indexDynamicRoute($route);
            }
        }
    }

    private function isStaticRoute(array $route): bool
    {
        return strpos($route['path'], '{') === false;
    }

    private function indexStaticRoute(array $route): void
    {
        $routeHash = md5($route['path']);
        foreach ($route['methods'] as $method) {
            $this->staticRoutes[$routeHash][$method] = $route;
        }
    }

    private function indexDynamicRoute(array $route): void
    {
        $this->dynamicRoutes[] = $route;
    }

    private function applyGlobalMiddleware(): void
    {
        $middlewareNames = array_map(
            fn($middleware) => basename(str_replace('\\', '/', $middleware)),
            self::GLOBAL_MIDDLEWARE
        );

        $this->allRoutes = array_map(
            fn($route) => $this->addMiddlewareToRoute($route, $middlewareNames),
            $this->allRoutes
        );
    }

    private function addMiddlewareToRoute(array $route, array $middleware): array
    {
        $route['middleware'] = array_merge(
            $route['middleware'] ?? [],
            $middleware
        );
        return $route;
    }
    public function dispatch(Request $request)
    {
        try {
            $route = $this->findMatchingRoute($request->getPathInfo(), $request->getMethod());
            if (!$route) {
                return $this->createErrorResponse('Route not found', 404);
            }

            return $this->handleRoute($route, $request);
        } catch (Throwable $e) {
            return $this->createErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function findMatchingRoute(string $path, string $method): ?array
    {
        $routeHash = md5($path);

        // Pencocokan rute statis menggunakan hash
        if (isset($this->staticRoutes[$routeHash][$method])) {
            return $this->staticRoutes[$routeHash][$method];
        }

        // Jika tidak ditemukan, coba rute dinamis
        return $this->findDynamicRoute($path, $method);
    }

    private function findDynamicRoute(string $path, string $method): ?array
    {
        foreach ($this->dynamicRoutes as $route) {
            // Periksa apakah metode HTTP cocok
            if (in_array($method, $route['methods'])) {
                // Buat pola untuk mencocokkan rute dinamis
                $pattern = $this->createRoutePattern($route['path']);
                if (preg_match($pattern, $path, $matches)) {
                    // Jika cocok, kembalikan rute
                    return $route;
                }
            }
        }
        return null;
    }

    private function createRoutePattern(string $routePath): string
    {
        static $patterns = [];

        if (!isset($patterns[$routePath])) {
            $pattern = preg_replace(
                '/\{[a-zA-Z_][a-zA-Z0-9_]*\}/',
                '([a-zA-Z0-9_-]+)?',
                $routePath
            );
            $patterns[$routePath] = '#^' . $pattern . '$#';
        }

        return $patterns[$routePath];
    }

    private function handleRoute(array $route, Request $request)
    {
        [$class, $method] = $route['handler'];

        $handler = $this->container->make($class);
        $params = $this->extractRouteParams($route['path'], $request->getPathInfo());

        $args = $this->resolveMethodArguments(
            $class,
            $method,
            $params,
            $request
        );

        $middlewares = $route['middleware'] ?? [];
        $response = $this->middlewareExecutor->execute($middlewares, $request, function() use ($handler, $method, $args) {
            return call_user_func_array([$handler, $method], $args);
        });

        return response()->json($response);
    }
    private function extractRouteParams(string $routePath, string $actualPath): array
    {
        $cacheKey = $routePath . '|' . $actualPath;
        if (isset($this->paramCache[$cacheKey])) {
            return $this->paramCache[$cacheKey];
        }

        $params = $this->doExtractRouteParams($routePath, $actualPath);

        // Validasi parameter
        $requiredParams = $this->getRequiredParams($routePath);

        foreach ($requiredParams as $paramName) {
            if (!isset($params[$paramName]) || $params[$paramName] === null) {
                throw new \InvalidArgumentException("Missing required parameter: {$paramName}", 400);
            }
        }

        // Validasi tipe data jika diperlukan
        $params = $this->validateParameters($params);

        $this->paramCache[$cacheKey] = $params;
        return $params;
    }

    private function getRequiredParams(string $routePath): array
    {
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $routePath, $matches);
        return $matches[1];  // Nama parameter dari path seperti {id}
    }

    private function validateParameters(array $params): array
    {
        foreach ($params as $key => $value) {
            if ($key === 'id' && !is_numeric($value)) {
                throw new \InvalidArgumentException("Parameter 'id' must be numeric.", 400);
            }

            if ($key === 'uuid' && !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $value)) {
                throw new \InvalidArgumentException("The 'uuid' parameter must be a valid UUID.", 400);
            }
        }

        return $params;
    }

    private function doExtractRouteParams(string $routePath, string $actualPath): array
    {
        // Ekstraksi parameter path yang sebenarnya
        $routeParts = $this->splitPath($routePath);
        $actualParts = $this->splitPath($actualPath);
        $params = [];

        foreach ($routeParts as $index => $part) {
            if ($this->isPathParameter($part)) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $actualParts[$index] ?? null;
            }
        }

        return $params;
    }

    private function splitPath(string $path): array
    {
        return array_values(array_filter(
            explode('/', $path),
            fn($part) => $part !== ''
        ));
    }

    private function isPathParameter(string $part): bool
    {
        return strpos($part, '{') === 0 &&
               strpos($part, '}') === strlen($part) - 1;
    }

    private function createErrorResponse(string $message, int $status)
    {
        return response()->json([
            'data' => null,
            'meta' => null,
            'error' => ['message' => $message]
        ], $status);
    }

    private function scanAttributeRoutes(): array
    {
        $routes = [];
        $handlerDir = app_path('src/Modules');

        foreach (glob($handlerDir . '/*/Http/*Handler.php') as $file) {
            $className = $this->resolveClassFromFile($file);
            if (!class_exists($className)) continue;

            $routes = array_merge(
                $routes,
                $this->extractRoutesFromClass($className)
            );
        }

        return $routes;
    }

    private function resolveClassFromFile(string $file): string
    {
        $namespace = $this->extractNamespace($file);
        $className = basename($file, '.php');
        return $namespace . '\\' . $className;
    }

    private function extractNamespace(string $file): string
    {
        $contents = file_get_contents($file);
        if (preg_match('/namespace\s+(.+);/', $contents, $matches)) {
            return $matches[1];
        }
        return '';
    }
    private function extractRoutesFromClass(string $className): array
    {
        static $classRoutes = [];

        if (isset($classRoutes[$className])) {
            return $classRoutes[$className];
        }

        $routes = [];
        $reflection = new ReflectionClass($className);

        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(Route::class);

            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                $routes[] = [
                    'path' => $route->path,
                    'methods' => $route->methods,
                    'middleware' => $route->middleware,
                    'handler' => [
                        $className,
                        $method->getName()
                    ],
                ];
            }
        }

        $classRoutes[$className] = $routes;
        return $routes;
    }

    private function resolveMethodArguments(string $handlerClass, string $method, array $params, Request $request): array
    {
        $cacheKey = "{$handlerClass}::{$method}";

        if (!isset($this->methodArgsCache[$cacheKey])) {
            $reflection = new ReflectionMethod($handlerClass, $method);
            $this->methodArgsCache[$cacheKey] = $this->analyzeMethodParameters($reflection->getParameters());
        }

        return $this->buildArgumentsList(
            $this->methodArgsCache[$cacheKey],
            $params,
            $request
        );
    }

    private function analyzeMethodParameters(array $parameters): array
    {
        return array_map(function($param) {
            $type = $param->getType();
            return [
                'name' => $param->getName(),
                'type' => $type instanceof \ReflectionNamedType ? $type->getName() : null,
                'isBuiltin' => $type instanceof \ReflectionNamedType ? $type->isBuiltin() : true,
                'isOptional' => $param->isOptional(),
                'defaultValue' => $param->isOptional() ? $param->getDefaultValue() : null
            ];
        }, $parameters);
    }

    private function buildArgumentsList(array $parameterInfo, array $params, Request $request): array
    {
        return array_map(function($info) use ($params, $request) {
            if ($info['type'] === Request::class) {
                return $request;
            }

            if (isset($params[$info['name']])) {
                return $params[$info['name']];
            }

            return $info['isOptional'] ? $info['defaultValue'] : null;
        }, $parameterInfo);
    }
}
