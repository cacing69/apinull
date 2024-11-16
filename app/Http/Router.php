<?php

namespace App\Http;

use App\Http\Middlewares\{
    CorsMiddleware,
    FixHeadersMiddleware,
    InputSanitizationMiddleware,
    AuthMiddleware
};
use App\Kernel\Container;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;
use ReflectionMethod;
use ReflectionClass;
use Throwable;

class Router
{
    /** @var array Registered routes */
    private array $routes;

    /** @var Container Dependency container */
    private Container $container;

    /** @var array Combined imported routes */
    private array $allRoutes = [];

    /** @var array Route lookup index */
    private array $routeIndex = [];

    /** @var array Routes with duplicate paths */
    private array $duplicates = [];

    /** @var array Middleware name to class mapping */
    private array $middlewareMap = [
        "auth" => AuthMiddleware::class,
    ];

    private string $cacheFile;

    /** @var array Global middleware applied to all routes */
    private array $globalMiddleware = [
        CorsMiddleware::class,
        FixHeadersMiddleware::class,
        InputSanitizationMiddleware::class,
    ];

    private array $compiledMiddleware = [];

    private array $resolvedHandlers = [];
    private array $methodArgsCache = [];

    public function __construct(string $configFile, Container $container)
    {
        $this->cacheFile = storage_path('framework/cache/routes.php');
        $this->container = $container;

        if ($this->shouldUseCache()) {
            $this->loadFromCache();
        } else {
            $this->routes = Yaml::parseFile($configFile);
            $this->initializeRoutes();
            $this->cacheRoutes();
        }
    }

    private function shouldUseCache(): bool
    {
        return $_ENV["APP_ROUTE_CACHED"] === 'true' &&
               file_exists($this->cacheFile) &&
               filemtime($this->cacheFile) > $this->getLastConfigModified();
    }

    private function loadFromCache(): void
    {
        $cached = require $this->cacheFile;
        $this->routeIndex = $cached['index'];
        $this->allRoutes = $cached['routes'];
    }

    private function cacheRoutes(): void
    {
        if ($_ENV["APP_ROUTE_CACHED"] === 'true') {
            $content = '<?php return ' . var_export([
                'index' => $this->routeIndex,
                'routes' => $this->allRoutes
            ], true) . ';';

            file_put_contents($this->cacheFile, $content);
        }
    }

    private function getLastConfigModified(): int
    {
        $configFiles = [
            // Main config file
            app_path('routes.yaml'),
            // Scan for all route files
            ...glob(app_path('src/Modules/**/*.routes.yaml'))
        ];

        return max(array_map('filemtime', $configFiles));
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
        if (!isset($this->routes['imports'])) {
            return;
        }

        foreach ($this->routes['imports'] as $import) {
            $this->processRouteImport($import);
        }
    }

    private function processRouteImport(array $import): void
    {
        $filePath = app_path($import['resource']);
        if (!file_exists($filePath)) {
            return;
        }

        $importedRoutes = Yaml::parseFile($filePath);
        $this->processRouteGroups($importedRoutes);
        $this->trackDuplicateRoutes($importedRoutes);

        $this->allRoutes = array_merge(
            $this->allRoutes,
            $importedRoutes['routes']
        );
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
        $this->routeIndex = [
            'static' => [],
            'dynamic' => []
        ];

        foreach ($this->allRoutes as $route) {
            $this->indexRoute($route);
        }
    }

    private function indexRoute(array $route): void
    {
        if (strpos($route['path'], '{') !== false) {
            $this->routeIndex['dynamic'][] = $route;
        } else {
            $this->routeIndex['static'][$route['path']] = $route;
        }
    }

    private function applyGlobalMiddleware(): void
    {
        $middlewareNames = array_map(
            fn($middleware) => basename(str_replace('\\', '/', $middleware)),
            $this->globalMiddleware
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
            $path = $request->getPathInfo();
            $method = $request->getMethod();

            $route = $this->findMatchingRoute($path);
            if (!$route) {
                return $this->createErrorResponse('Route not found', 404);
            }

            if (!in_array($method, $route['methods'])) {
                return $this->createErrorResponse("Method {$method} not allowed", 405);
            }

            return $this->handleRoute($route, $request);
        } catch (Throwable $e) {
            return response_error($e->getMessage());
        }
    }

    private function findMatchingRoute(string $path): ?array
    {
        // Check static routes first
        if (isset($this->routeIndex['static'][$path])) {
            return $this->routeIndex['static'][$path];
        }

        // Check dynamic routes
        return $this->findDynamicRoute($path);
    }

    private function findDynamicRoute(string $path): ?array
    {
        foreach ($this->routeIndex['dynamic'] as $route) {
            $pattern = $this->createRoutePattern($route['path']);
            if (preg_match($pattern, $path)) {
                return $route;
            }
        }
        return null;
    }

    private function createRoutePattern(string $routePath): string
    {
        $pattern = preg_replace(
            '/\{[a-zA-Z_][a-zA-Z0-9_]*\}/',
            '([a-zA-Z0-9_-]+)',
            $routePath
        );
        return '#^' . $pattern . '$#';
    }

    private function handleRoute(array $route, Request $request)
    {
        [$class, $method] = explode('::', $route['handler']);
        $handler = $this->container->make($class);
        $params = $this->extractRouteParams($route['path'], $request->getPathInfo());

        // $reflectionMethod = new ReflectionMethod($class, $method);

        $args = $this->resolveMethodArguments(
            $class,
            $method,
            $params,
            $request
        );

        $middlewares = $route['middleware'] ?? [];
        $response = $this->executeMiddlewareChain($middlewares, $request, function() use ($handler, $method, $args) {
            return call_user_func_array([$handler, $method], $args);
        });

        return response()->json($response);
    }

    private function extractRouteParams(string $routePath, string $actualPath): array
    {
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

    private function executeMiddlewareChain(array $middlewares, Request $request, callable $final)
    {
        $compiled = $this->compileMiddleware($middlewares);

        $pipeline = array_reduce(
            array_reverse($compiled),
            function ($next, $middleware) {
                return function ($request) use ($next, $middleware) {
                    return $middleware->handle($request, $next);
                };
            },
            $final
        );

        return $pipeline($request);
    }

    private function resolveMiddlewareClass(string $middleware): string
    {
        return $this->middlewareMap[$middleware]
            ?? "App\\Http\\Middlewares\\{$middleware}";
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

        foreach (glob($handlerDir . '/*/Http/*.php') as $file) {
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
                    'handler' => $className . '::' . $method->getName(),
                ];
            }
        }

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

    private function resolveBuiltinParameter(\ReflectionParameter $param, array $params)
    {
        return $params[$param->getName()]
            ?? ($param->isOptional() ? $param->getDefaultValue() : null);
    }

    private function resolveComplexParameter(
        \ReflectionParameter $param,
        \ReflectionNamedType $type,
        array $params,
        Request $request
    ) {
        if ($type->getName() === Request::class) {
            return $request;
        }

        return $params[$param->getName()] ?? null;
    }

    private function compileMiddleware(array $middlewares): array
    {
        $key = implode('|', $middlewares);

        if (!isset($this->compiledMiddleware[$key])) {
            $this->compiledMiddleware[$key] = array_map(
                fn($middleware) => $this->resolveMiddlewareInstance($middleware),
                $middlewares
            );
        }

        return $this->compiledMiddleware[$key];
    }

    private function resolveMiddlewareInstance(string $middleware): object
    {
        $class = $this->middlewareMap[$middleware]
            ?? "App\\Http\\Middlewares\\{$middleware}";

        return new $class();
    }

    private function resolveHandler(string $handlerClass): object
    {
        if (!isset($this->resolvedHandlers[$handlerClass])) {
            $this->resolvedHandlers[$handlerClass] = $this->container->make($handlerClass);
        }

        return $this->resolvedHandlers[$handlerClass];
    }
}
