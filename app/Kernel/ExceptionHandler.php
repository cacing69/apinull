<?php

namespace App\Kernel;

use App\Kernel\LogManager;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class ExceptionHandler
{
    private $logger;
    protected $whoops;

    public function __construct()
    {
        $this->whoops = new Run();
        // Inisialisasi logger
        $logManager = new LogManager();
        $this->logger = $logManager->getLogger();
    }

    public function handle(\Throwable $exception)
    {

        if (filter_var(@$_REQUEST["render_error"], FILTER_VALIDATE_BOOL)) {
            // Mode lokal - tampilkan halaman error dengan PrettyPageHandler
            $this->whoops->pushHandler(new PrettyPageHandler());
            $this->whoops->handleException($exception);
        } else {

            $httpCode = preg_match('/^(?:4|5)\d{2}$/', $exception->getCode()) ? $exception->getCode() : 500;
            return response()->json([
                "data" => null,
                "meta" => null,
                "error" => [
                    "message" => $this->getErrorMessage($exception),
                    "stacks" => [
                        [
                            "type" => get_class($exception),
                            "trace" => $exception->getMessage(),
                            "file" => $exception->getFile(),
                            "line" => $exception->getLine(),
                        ]
                    ]
                ]
            ], $httpCode);
        }
    }

    private function getErrorMessage(\Throwable $throwable)
    {
        if(preg_match('/SQLSTATE\[.*\]\:(.*)\:.*\sERROR/', $throwable->getMessage(), $extractMessage)) {
            return trim(strtolower($extractMessage[1]));
        } else {
            return $throwable->getMessage();
        }
    }
}
