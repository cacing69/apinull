<?php

namespace App\Http;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route
{
    public string $path;
    public array $methods;
    public array $handler;

    public array $middleware;
    public function __construct(string $path, array $methods = ['GET'], array $middleware = [])
    {
        $this->path = $path;
        $this->methods = $methods;
        $this->middleware = $middleware;
    }
}
