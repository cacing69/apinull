<?php

namespace App\Http;

use Attribute;

/**
 * Class Route
 *
 * The Route class represents a custom attribute used to define HTTP routes
 * for a controller method or class. This class provides the structure for
 * associating a specific HTTP path, HTTP methods (GET, POST, etc.), and
 * middleware to a route. It is used in combination with other routing mechanisms
 * to determine how requests should be handled by the application.
 *
 * This class is designed to be used as an attribute on methods or classes
 * that define routes within the application.
 *
 * @package App\Http
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route
{
    /**
     * The URI path that the route matches.
     *
     * This is the URL pattern that the route will respond to. It can include dynamic
     * parameters in curly braces (e.g., `/user/{id}`) to capture values from the URL.
     *
     * @var string
     */
    public string $path;

    /**
     * The HTTP methods that this route will respond to.
     *
     * The methods define the types of HTTP requests (e.g., GET, POST, PUT, DELETE)
     * that the route will handle. By default, the route responds to the GET method
     * if no specific methods are provided.
     *
     * @var array
     */
    public array $methods;

    /**
     * The middleware to be applied to this route.
     *
     * Middleware functions are applied to the route before or after the route's
     * handler executes. Middleware can be used to handle cross-cutting concerns such
     * as authentication, logging, or input validation.
     *
     * The middleware will be executed in the order they are listed.
     *
     * @var array
     */
    public array $middleware;

    /**
     * Constructor to initialize the Route attribute.
     *
     * The constructor accepts the route path, the HTTP methods that the route will
     * handle, and any middleware to be applied to the route. Middleware is optional
     * and defaults to an empty array if not provided.
     *
     * @param string $path The URI path to which the route responds.
     * @param array $methods An array of HTTP methods the route responds to (default: ['GET']).
     * @param array $middleware An array of middleware to apply to the route (default: empty array).
     */
    public function __construct(string $path, array $methods = ['GET'], array $middleware = [])
    {
        $this->path = $path;
        $this->methods = $methods;
        $this->middleware = $middleware;
    }
}
