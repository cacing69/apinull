<?php
namespace App\Http\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Menambahkan header CORS
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Untuk menangani preflight request
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 204);
        }

        return $next($request);
    }
}
