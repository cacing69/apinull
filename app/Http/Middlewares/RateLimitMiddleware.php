<?php

namespace App\Http\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    private $rateLimit = 100; // Batas permintaan per waktu
    private $timeWindow = 60 * 60; // Jangka waktu dalam detik (1 jam)
    private static $cache = []; // Penyimpanan in-memory untuk tracking permintaan

    public function handle(Request $request, callable $next)
    {
        $clientId = $this->getClientId($request);
        $currentTime = time();

        // Inisialisasi data permintaan jika belum ada
        if (!isset(self::$cache[$clientId])) {
            self::$cache[$clientId] = [
                'count' => 0,
                'startTime' => $currentTime
            ];
        }

        // Reset counter jika waktu window sudah habis
        if ($currentTime - self::$cache[$clientId]['startTime'] > $this->timeWindow) {
            self::$cache[$clientId] = [
                'count' => 0,
                'startTime' => $currentTime
            ];
        }

        // Tambahkan hitungan permintaan
        self::$cache[$clientId]['count']++;

        // Jika permintaan melebihi batas, kirimkan respons 429
        if (self::$cache[$clientId]['count'] > $this->rateLimit) {
            return new Response(
                json_encode(['error' => 'Too many requests']),
                429,
                ['Content-Type' => 'application/json']
            );
        }

        // Lanjutkan ke middleware berikutnya atau handler jika di bawah batas
        return $next($request);
    }

    private function getClientId(Request $request)
    {
        // Mendapatkan API Key dari header Authorization
        $apiKey = $request->headers->get('Authorization');
        return $apiKey ? md5($apiKey) : $request->getClientIp(); // fallback ke IP jika tidak ada API Key
    }
}
