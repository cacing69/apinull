<?php
namespace App\Http\Middlewares;
use Illuminate\Http\Request;

class FixHeadersMiddleware
{
    public function handle(Request $request, callable $next)
    {
        // Menambahkan header keamanan
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("Content-Security-Policy: default-src 'self'");

        return $next($request);
    }
}
