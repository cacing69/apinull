<?php

namespace App\Http\Middlewares;  // Declaring the middleware namespace

use Symfony\Component\HttpFoundation\Request;  // Importing the Request class for working with incoming HTTP requests
use Symfony\Component\HttpFoundation\Response;  // Importing the Response class for creating HTTP responses

class RateLimitMiddleware
{
    private $rateLimit = 100; // The maximum number of requests allowed per time window (default: 100 requests)
    private $timeWindow = 60 * 60; // The time window duration in seconds (default: 1 hour)
    private static $cache = []; // In-memory cache to store the request count for each client

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
    private function getClientId(Request $request)
    {
        // Get the API key from the Authorization header (if available)
        $apiKey = $request->headers->get('Authorization');
        return $apiKey ? md5($apiKey) : $request->getClientIp();  // Use API key (hashed) or fallback to IP address
    }
}
