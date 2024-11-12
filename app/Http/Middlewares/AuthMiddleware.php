<?php

namespace App\Http\Middlewares;
use App\Kernel\LogManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    private $logger;

    public function __construct()
    {

        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();
    }
    public function handle(Request $request, callable $next)
    {
        // $request = Request::createFromGlobals();

        $authHeader = 'Bearer xyz.abc';

        if (!$authHeader) {
            // $this->logger->warning('Bearer token not provided', [
            //     'uri' => $request->getPathInfo(),
            // ]);
        }

        // Lakukan otentikasi atau cek sesi pengguna
        $isAuthenticated = true; // Ganti dengan logika autentikasi yang sesuai

        if (!$isAuthenticated) {
            // $this->logger->warning('Unauthorized access attempt', [
            //     'uri' => $request->getPathInfo(),
            // ]);
            return new Response(
                json_encode(['error' => 'unauthorized']),
                401,
                ['Content-Type' => 'application/json']
            );
        }

        // Jika otentikasi berhasil, teruskan permintaan
        return $next($request); // Pastikan ini mengembalikan Response
    }
}
