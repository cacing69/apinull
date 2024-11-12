<?php

namespace App\Kernel;
use App\Kernel\LogManager;
use Symfony\Component\HttpFoundation\Response;

class ExceptionHandler
{
    private $logger;

    public function __construct()
    {
        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();
    }
    public function handle(\Throwable $exception) : Response
    {
        // Mengatur kode status HTTP berdasarkan jenis exception
        $statusCode = 500; // Default: Internal Server Error

        // Mengatur pesan error berdasarkan jenis exception
        $errorMessage = 'internal server error';

        // Cek jenis exception dan atur pesan yang sesuai
        if ($exception instanceof \Error) {
            $statusCode = 500; // Atur kode status untuk Error
            $errorMessage = $exception->getMessage();
        } elseif ($exception instanceof \Exception) {
            $statusCode = 400; // Bad Request untuk Exception
            $errorMessage = $exception->getMessage();
        }

        // Mengatur header response
        http_response_code($statusCode);
        header('Content-Type: application/json');

         // Catat kesalahan yang terjadi
        // $this->logger->error('Exception caught', [
        //     'message' => $exception->getMessage(),
        //     'stack' => $exception->getTraceAsString(),
        // ]);

        // Mengembalikan respons dalam bentuk array
        // Mengembalikan respons dalam bentuk JSON
        return new Response(
            json_encode(['error' => $errorMessage, 'code' => $statusCode]),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }
}
