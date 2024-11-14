<?php
namespace App\Http\Middlewares;
use Illuminate\Http\Request;


class CorsMiddleware
{
    public function handle(Request $request, callable $next)
    {
        // Menambahkan header CORS
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Untuk menangani preflight request
        if ($request->getMethod() === 'OPTIONS') {
            return response()->json([], 204);
        }

        return $next($request);
    }
}
