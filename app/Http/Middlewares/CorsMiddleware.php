<?php
namespace App\Http\Middlewares;  // Declare the namespace for the middleware

use Illuminate\Http\Request;  // Import the Request class to access HTTP request data

/**
 * CORS Middleware for handling Cross-Origin Resource Sharing (CORS) headers.
 */
class CorsMiddleware
{
    /**
     * Handle the incoming request and add CORS headers.
     *
     * This middleware adds the necessary CORS headers to allow cross-origin requests
     * from any origin, with specific HTTP methods, and custom headers. It also handles
     * preflight OPTIONS requests by responding with a 204 status code and the appropriate headers.
     *
     * @param Request $request The incoming HTTP request.
     * @param callable $next A callback to pass the request to the next middleware.
     * @return mixed The response after handling the CORS headers and processing the request.
     */
    public function handle(Request $request, callable $next)
    {
        // Add Access-Control-Allow-Origin header to allow all origins
        header("Access-Control-Allow-Origin: *");

        // Add Access-Control-Allow-Methods header to specify allowed HTTP methods
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");

        // Add Access-Control-Allow-Headers header to specify allowed headers in the request
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Check if the request is a preflight OPTIONS request (used for CORS checks before actual requests)
        if ($request->getMethod() === 'OPTIONS') {
            // Respond with an empty body and 204 No Content status code for preflight requests
            return response()->json([], 204);
        }

        // Pass the request to the next middleware or handler in the pipeline
        return $next($request);
    }
}
