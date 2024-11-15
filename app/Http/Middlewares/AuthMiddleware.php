<?php

namespace App\Http\Middlewares;
// use App\Kernel\LogManager;
use Illuminate\Http\Request;

class AuthMiddleware
{
    private $logger;

    public function __construct()
    {

        // Inisialisasi logger
        // $logManager = new LogManager();
        // $this->logger = $logManager->getLogger();
    }
    public function handle(Request $request, callable $next)
    {
        if(token_check()) {
            return $next($request);
        }
    }
}
