<?php

namespace App\Http\Middlewares;

use App\Core\LogManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseMiddleware
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
        // Tambahkan log untuk melihat kapan middleware dipanggil
        $this->logger->info('JsonResponseMiddleware: Handling request', [
            'uri' => $request->getPathInfo(),
            'method' => $request->getMethod(),
        ]);

        // Lanjutkan ke handler berikutnya untuk mendapatkan response
        $response = $next($request);

        // Cek apakah respons berupa array atau object, dan ubah ke JSON jika ya
        $content = $response->getContent();

        // Logging untuk melihat konten
        $this->logger->info('JsonResponseMiddleware: Response content', [
            'content' => $content,
        ]);

        // Mengubah konten ke JSON jika merupakan array atau objek
        if (is_array($content) || is_object($content)) {
            $jsonContent = json_encode($content);
            // Log jika terjadi error pada json_encode
            if ($jsonContent === false) {
                $this->logger->error('JSON encoding error', [
                    'error' => json_last_error_msg(),
                ]);
                return new Response(
                    json_encode(['error' => 'Internal Server Error']),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    ['Content-Type' => 'application/json']
                );
            }

            return new Response(
                $jsonContent,
                $response->getStatusCode(),
                ['Content-Type' => 'application/json']
            );
        }

        // Mengembalikan respons asli jika tidak perlu diubah
        return $response;
    }
}
