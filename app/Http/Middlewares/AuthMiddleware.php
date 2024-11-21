<?php

namespace App\Http\Middlewares;

use Illuminate\Http\Request;

class AuthMiddleware
{
    private $logger;
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
