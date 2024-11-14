<?php

namespace App\Kernel;
use App\Kernel\LogManager;

class ExceptionHandler
{
    private $logger;

    public function __construct()
    {
        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();
    }
    public function handle(\Throwable $exception)
    {

        // dd($exception);
        // Mengatur kode status HTTP berdasarkan jenis exception
        $statusCode = $exception->getCode() ?? 500; // Default: Internal Server Error

        // Mengatur pesan error berdasarkan jenis exception
        // $errorMessage = 'internal server error';

        // Cek jenis exception dan atur pesan yang sesuai
        // if ($exception instanceof \Error) {
            // $statusCode = 500; // Atur kode status untuk Error
            // $errorMessage = $exception->getMessage();
        // } elseif ($exception instanceof \Exception) {
            // $statusCode = 400; // Bad Request untuk Exception
            // $errorMessage = $exception->getMessage();
        // }

        // Mengatur header response
        // http_response_code($statusCode);
        // header('Content-Type: application/json');

         // Catat kesalahan yang terjadi
        // $this->logger->error('Exception caught', [
        //     'message' => $exception->getMessage(),
        //     'stack' => $exception->getTraceAsString(),
        // ]);

        // dd(1);

        // Mengembalikan respons dalam bentuk array
        // Mengembalikan respons dalam bentuk JSON
        return response()->json([
            "data" => null,
            "meta" => null,
            'error' => [
                "message" => $exception->getMessage()
            ]
        ] ,$statusCode);
    }
}
