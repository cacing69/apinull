<?php
namespace App\Http\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InputSanitizationMiddleware {
    public function handle(Request $request, callable $next): Response {
        // Sanitasi input
        $request->request->replace(array_map('htmlspecialchars', $request->request->all()));

        return $next($request);
    }
}
