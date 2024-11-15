<?php

namespace App\Http\Middlewares;  // Declaring the middleware namespace

use Symfony\Component\HttpFoundation\Request;  // Importing the Request class for working with incoming HTTP requests
use Symfony\Component\HttpFoundation\Response;  // Importing the Response class for creating HTTP responses

/**
 * RateLimitMiddleware class to limit the number of requests a client can make within a time window.
 *
 * This middleware implements a basic rate-limiting feature that prevents clients from making too many requests
 * in a given time period (default: 1 hour). If the limit is exceeded, it returns a 429 Too Many Requests response.
 */
class RateLimitMiddleware
{
    private $rateLimit = 100; // The maximum number of requests allowed per time window (default: 100 requests)
    private $timeWindow = 60 * 60; // The time window duration in seconds (default: 1 hour)
    private static $cache = []; // In-memory cache to store the request count for each client

    /**
     * Handle the incoming request and apply rate limiting logic.
     *
     * This method checks the number of requests made by a client in the given time window.
     * If the client exceeds the rate limit, a 429 Too Many Requests response is returned.
     * If the client is within the allowed limit, the request is passed to the next middleware or handler.
     *
     * @param Request $request The incoming HTTP request.
     * @param callable $next A callback to pass the request to the next middleware or handler.
     * @return Response The HTTP response after applying rate limiting.
     */
    public function handle(Request $request, callable $next)
    {
        $clientId = $this->getClientId($request);  // Get the client's unique identifier (API key or IP address)
        $currentTime = time();  // Get the current timestamp

        // Initialize request tracking for the client if it doesn't exist
        if (!isset(self::$cache[$clientId])) {
            self::$cache[$clientId] = [
                'count' => 0,  // Request count for the client
                'startTime' => $currentTime  // Time when the current rate-limiting window started
            ];
        }

        // Reset the request counter if the time window has expired
        if ($currentTime - self::$cache[$clientId]['startTime'] > $this->timeWindow) {
            self::$cache[$clientId] = [
                'count' => 0,  // Reset count to 0
                'startTime' => $currentTime  // Start a new time window
            ];
        }

        // Increment the request count
        self::$cache[$clientId]['count']++;

        // If the client exceeds the rate limit, return a 429 Too Many Requests response
        if (self::$cache[$clientId]['count'] > $this->rateLimit) {
            return new Response(
                json_encode(['error' => 'Too many requests']),  // JSON response with error message
                429,  // HTTP status code 429: Too Many Requests
                ['Content-Type' => 'application/json']  // Set Content-Type to application/json
            );
        }

        // If within the limit, proceed to the next middleware or request handler
        return $next($request);
    }

    /**
     * Get the unique client identifier based on the request.
     *
     * This method attempts to retrieve an API key from the Authorization header. If no API key is provided,
     * it falls back to using the client's IP address as the unique identifier.
     *
     * @param Request $request The incoming HTTP request.
     * @return string The unique client identifier (either API key or IP address).
     */
    private function getClientId(Request $request)
    {
        // Get the API key from the Authorization header (if available)
        $apiKey = $request->headers->get('Authorization');
        return $apiKey ? md5($apiKey) : $request->getClientIp();  // Use API key (hashed) or fallback to IP address
    }
}
