<?php
namespace App\Http\Middlewares;  // Declare the namespace for the middleware

use Illuminate\Http\Request;  // Import the Request class to access HTTP request data

/**
 * FixHeadersMiddleware to add security headers to HTTP responses.
 */
class FixHeadersMiddleware
{
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
