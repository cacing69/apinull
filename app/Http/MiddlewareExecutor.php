<?php
namespace App\Http;

use App\Http\Middlewares\AuthMiddleware;
use Illuminate\Http\Request;
// Kelas Middleware
class MiddlewareExecutor
{
    private array $middlewareMap = [
        "auth" => AuthMiddleware::class,
    ];

    private array $compiledMiddleware = [];

    public function execute(array $middlewares, Request $request, callable $final)
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
        return new ($this->getMiddlewareClass($middleware));
    }

    private function getMiddlewareClass($string): string
    {
        if (str_contains($string, '\\')) {
            return $string;
        } else {
            return $this->middlewareMap[$string]
            ?? "App\\Http\\Middlewares\\{$string}";
        }
    }
}
