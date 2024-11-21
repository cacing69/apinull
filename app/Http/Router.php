<?php

namespace App\Http;

class Router
{
    private array $routes = [];
    private ?string $group = null;
    private ?array $middleware = [];

    private function checkSlashOnPath($path)
    {
        return str_starts_with($path, "/") ? $path : "/" . $path;
    }
    public function add(string $path, array $handler, array $methods = ['GET'], array $middleware = []): self
    {
        $this->routes[] = [
            'path' => $this->checkSlashOnPath($path),
            'handler' => $handler,
            'methods' => $methods,
            'middleware' => $middleware,
        ];
        return $this;
    }

    public function get(string $path, array $handler, array $middleware = []): self
    {
        $this->add($path, $handler, ["GET"], $middleware);

        return $this;
    }

    public function post(string $path, array $handler, array $middleware = []): self
    {
        $this->add($path, $handler, ["POST"], $middleware);

        return $this;
    }

    public function put(string $path, array $handler, array $middleware = []): self
    {
        $this->add($path, $handler, ["PUT"], $middleware);

        return $this;
    }

    public function patch(string $path, array $handler, array $middleware = []): self
    {
        $this->add($path, $handler, ["PATCH"], $middleware);

        return $this;
    }

    public function delete(string $path, array $handler, array $middleware = []): self
    {
        $this->add($path, $handler, ["DELETE"], $middleware);

        return $this;
    }

    public function match(array $methods, string $path, array $handler, array $middleware = []): self
    {
        $this->add($path, $handler, $methods, $middleware);

        return $this;
    }

    public function take(): array
    {
        return [
            "group" => $this->group,
            "middleware" => $this->middleware,
            "routes" => $this->routes
        ];
    }
}
