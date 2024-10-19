<?php
namespace App\Http\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FixHeadersMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Menambahkan header keamanan
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("Content-Security-Policy: default-src 'self'");

        return $next($request);
    }
}
