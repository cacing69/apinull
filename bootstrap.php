<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Http\Router;
use App\Http\Middlewares\JsonResponseMiddleware;
use Symfony\Component\HttpFoundation\Request;

// Inisialisasi database menggunakan Singleton
Database::getInstance();

// Membuat objek Request dari globals (data dari request HTTP saat ini)
$request = Request::createFromGlobals();

// Inisialisasi router dan membaca rute dari file YAML
$router = new Router(__DIR__ . '/config/routes.yaml');

// Mendistribusikan request dan mendapatkan respons yang sesuai dari handler
$response = $router->dispatch($request);

// Inisialisasi middleware untuk mengonversi respons menjadi JSON
$middleware = new JsonResponseMiddleware();

// Jalankan middleware dan kirim respons JSON ke klien
$jsonResponse = $middleware->handle($request, function($request) use ($response) {
    // Mengembalikan respons asli ke middleware untuk diproses lebih lanjut
    return $response;
});

// Kirim respons JSON ke klien
$jsonResponse->send();
