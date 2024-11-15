<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel\InitDB;
use App\Http\Router;
use Dotenv\Dotenv;
use Illuminate\Http\Request;

define("APINULL_PATH", __DIR__);


$container = new \App\Kernel\Container();

// Membaca konfigurasi dari containers.php dan melakukan binding ke container
$repositories = require_once __DIR__ .DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'containers.php';

foreach ($repositories as $abstract => $concrete) {
    $container->bind($abstract, $concrete);
}

// preg_match('/localhost:\d{4}/', "asdasdasd.asdasd.asdas", $matches);
// ;
// dd(preg_match('/^localhost:\d{4}$/', $_SERVER['HTTP_HOST']."0"));

if(preg_match('/^(localhost|127\.0\.0\.1):\d{4}$/', $_SERVER['HTTP_HOST'])) {
    // Inisialisasi dotenv
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Baca variabel dari .env
// $this->rateLimit = getenv('RATE_LIMIT') ?: 100;
// $this->timeWindow = getenv('TIME_WINDOW') ?: 3600;

// Inisialisasi database menggunakan Singleton
InitDB::getInstance();

// Membuat objek Request dari globals (data dari request HTTP saat ini)
$request = Request::capture();

// Inisialisasi router dan membaca rute dari file YAML
$router = new Router(__DIR__ . '/routes.yaml', $container);

// Mendistribusikan request dan mendapatkan respons yang sesuai dari handler
$response = $router->dispatch($request);

$response->send();
// Inisialisasi middleware untuk mengonversi respons menjadi JSON
// $middleware = new JsonResponseMiddleware();

// // Jalankan middleware dan kirim respons JSON ke klien
// $jsonResponse = $middleware->handle($request, function($request) use ($response) {
//     // Mengembalikan respons asli ke middleware untuk diproses lebih lanjut
//     return $response;
// });

// // Kirim respons JSON ke klien
// $jsonResponse->send();
