<?php
namespace App\Http\Middlewares;  // Declare the namespace for the middleware

use Illuminate\Http\Request;  // Import the Request class to access HTTP request data

/**
 * FixHeadersMiddleware to add security headers to HTTP responses.
 */
class FixHeadersMiddleware
{
    /**
     * Handle the incoming request and add security headers.
     *
     * This middleware checks the environment and, if it's a production environment,
     * it adds security-related HTTP headers to the response to mitigate various
     * types of attacks like MIME sniffing, clickjacking, and XSS.
     *
     * @param Request $request The incoming HTTP request.
     * @param callable $next A callback to pass the request to the next middleware.
     * @return mixed The response after adding the headers and processing the request.
     */
    public function handle(Request $request, callable $next)
    {
        // Add security headers if the application is running in production or prod environment
        if (in_array(strtolower(getenv("APP_ENV")), ["prod", "production"])) {
            // Prevent MIME type sniffing
            header("X-Content-Type-Options: nosniff");

            // Deny embedding the website in a frame to prevent clickjacking attacks
            header("X-Frame-Options: DENY");

            // Restrict content loading to the same origin (self), reducing XSS risks
            header("Content-Security-Policy: default-src 'self'");
        }

        // Pass the request to the next middleware in the stack
        return $next($request);
    }
}
