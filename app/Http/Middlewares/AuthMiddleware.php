<?php

namespace App\Http\Middlewares;
use App\Core\LogManager;
use Symfony\Component\HttpFoundation\Request;
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
    public function handle(Request $request, callable $next): Response
    {
        $request = Request::createFromGlobals();

        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            $this->logger->warning('Bearer token not provided', [
                'uri' => $request->getPathInfo(),
            ]);
        }

        // Lakukan otentikasi atau cek sesi pengguna
        $isAuthenticated = true; // Ganti dengan logika autentikasi yang sesuai

        if (!$isAuthenticated) {
            $this->logger->warning('Unauthorized access attempt', [
                'uri' => $request->getPathInfo(),
            ]);
            return new Response(
                json_encode(['error' => 'unauthorized']),
                401,
                ['Content-Type' => 'application/json']
            );
        }

        // Jika otentikasi berhasil, teruskan permintaan
        return $next($request); // Pastikan ini mengembalikan Response
    }
    // public function handle($request, $next)
    // {
    //     $this->logger->info('AuthMiddleware: Handling request', [
    //         'uri' => $request->getPathInfo(),
    //         'method' => $request->getMethod(),
    //     ]);

    //     // Cek token autentikasi di header Authorization
    //    // Buat objek request Symfony
    //     $request = Request::createFromGlobals();

    //     // Cek header Authorization
    //     $authHeader = $request->headers->get('Authorization');

    //     if (!$authHeader) {
    //         $this->logger->warning('Unauthorized access attempt', [
    //             'uri' => $request->getPathInfo(),
    //         ]);

    //         // Mengembalikan respons error tanpa exit


    //         return ['error' => 'unauthorized'];

    //     }
    //     // logic check token

    //     // Lanjutkan ke handler berikutnya jika autentikasi berhasil
    //     return $next($request);
    // }
}
