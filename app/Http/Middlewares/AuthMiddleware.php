<?php

namespace App\Http\Middlewares;

use Illuminate\Http\Request;

/**
 * Class AuthMiddleware
 *
 * Middleware for handling authentication in the application.
 * It checks if a valid authentication token is present and authorized before
 * passing the request to the next middleware or handler.
 * If the authentication check fails, the request will not proceed further.
 *
 * @package App\Http\Middlewares
 */
class AuthMiddleware
{
    /**
     * @var mixed $logger
     * A logger instance for logging activities related to authentication.
     * (Note: Logger is currently commented out in the code)
     */
    private $logger;

    /**
     * Handle the incoming HTTP request and perform authentication check.
     *
     * This method checks if the incoming request has a valid authentication token.
     * If the token is valid, the request is allowed to proceed to the next middleware
     * or handler. If the token is invalid or missing, it prevents further processing.
     *
     * @param Request $request The incoming HTTP request.
     * @param callable $next The next middleware or handler to be called.
     * @return mixed The response from the next middleware or handler, or early return if authentication fails.
     */
    public function handle(Request $request, callable $next)
    {
        // Perform token check. If valid, continue to the next handler.
        if (token_check()) {
            return $next($request);
        }

        // Optionally, log a failed authentication attempt
        // $this->logger->error('Authentication failed: Invalid or missing token.');

        // If the token check fails, prevent the request from proceeding further
        // Optionally, return a response indicating that authentication is required
        // return response()->json(['error' => 'Unauthorized'], 401);
    }
}
