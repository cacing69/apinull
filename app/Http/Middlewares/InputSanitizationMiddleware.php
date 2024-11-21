<?php
namespace App\Http\Middlewares;  // Declaring the namespace for the middleware

use Illuminate\Http\Request;  // Importing the Request class to work with the HTTP request data

/**
 * InputSanitizationMiddleware class to sanitize input data in incoming HTTP requests.
 *
 * This middleware applies sanitization to the request data to prevent issues
 * such as Cross-Site Scripting (XSS) attacks and removes unnecessary whitespace.
 */
class InputSanitizationMiddleware
{
    public function handle(Request $request, callable $next)
    {
        // Sanitizing input by applying htmlspecialchars to all request parameters
        // htmlspecialchars is used to convert special characters to HTML entities
        $request->request->replace(array_map('htmlspecialchars', $request->request->all()));

        // Trim any unnecessary whitespace from input values
        $request->request->replace(array_map('trim', $request->request->all()));

        // Pass the sanitized request to the next middleware in the stack
        return $next($request);
    }
}
