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

            $errorDetail = $this->getErrorMessage($exception);
            return response()->json([
                "data" => null,
                "meta" => null,
                "error" => [
                    "message" => $this->getErrorMessage($exception)["message"],
                    "stacks" => @$_ENV["APP_ENV"] === "local" ? [
                         [
                            "type" => get_class($exception),
                            "trace" => $exception->getMessage(),
                            "file" => $exception->getFile(),
                            "line" => $exception->getLine(),
                        ]
                    ] : null
                ]
            ], $errorDetail["code"]);
        }
    }

    private function getErrorMessage(\Throwable $throwable)
    {
        if(preg_match('/SQLSTATE\[.*\]\:(.*)\:.*\sERROR/', $throwable->getMessage(), $extractMessage)) {
            return [
                "message" => trim(strtolower($extractMessage[1])),
                "code" => 500,
            ];
        } elseif(preg_match('/(No query results).*\[.*\]/', $throwable->getMessage(), $extractMessage)) {
            return [
                "message" => trim(strtolower($extractMessage[1])),
                "code" => 404,
            ];
        } else {
            return [
                "message" => $throwable->getMessage(),
                "code" => preg_match('/^(?:4|5)\d{2}$/', $throwable->getCode()) ? $throwable->getCode() : 500,
            ];
        }
    }
}
