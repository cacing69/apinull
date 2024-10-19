<?php

require_once __DIR__ . '/vendor/autoload.php';

// use Modules\User\Handlers\UserHandler;
use App\Core\ServiceContainer;
use App\Http\Router;
use App\Http\Middlewares\JsonResponseMiddleware;
use Symfony\Component\HttpFoundation\Request;

// function loadRoutes() {
//     $rootRoutes = Yaml::parseFile(__DIR__ . '/configs/routes.yaml');
//     $allRoutes = [];

//     // Memuat file routing root
//     if (isset($rootRoutes['imports'])) {
//         foreach ($rootRoutes['imports'] as $import) {
//             $importedRoutes = Yaml::parseFile($import['resource']);
//             $allRoutes = array_merge($allRoutes, $importedRoutes['routes']);
//         }
//     }

//     return $allRoutes;
// }
// Baca rute dari file YAML
$request = Request::createFromGlobals();

$serviceContainer = new ServiceContainer();

$serviceContainer->set('request', $request);

// dd($serviceContainer);
// Misalnya di index.php atau di tempat lain saat menginisialisasi aplikasi
$router = new Router(__DIR__ . '/config/routes.yaml', $serviceContainer);

// Ambil request URI dari server
// $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Buat objek Request dari globals


// // Fungsi untuk menjalankan middleware
// function runMiddleware($request, $handler) {
//     // Inisiasi middleware
//     $middleware = new JsonResponseMiddleware();

//     // Jalankan handler melalui middleware
//     return $middleware->handle($request, function($request) use ($handler) {
//         // Jalankan handler yang sesuai
//         return $handler->handle($request);
//     });
// }

// Dapatkan respons dari handler yang sesuai
$response = $router->dispatch($request);

// // Jika tidak ditemukan rute yang sesuai, berikan status code 404
// // Dispatch request dan ambil respons
// $response = $router->dispatch($request);

// // Kirim respons ke klien
// $response->send();

// function handleRequest() {
//     $routes = loadRoutes();
//     $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//     $requestMethod = $_SERVER['REQUEST_METHOD'];

//     // $test = new UserHandler();

//     // var_dump($test);



//     foreach ($routes as $route) {
//         if ($route['path'] === $requestUri && in_array($requestMethod, $route['methods'])) {
//             $handlerInfo = explode('::', $route['handler']);
//             $handlerClass = 'Modules\\' . $handlerInfo[0];
//             $handlerMethod = $handlerInfo[1];


//             // var_dump($handlerClass, class_exists($handlerClass));
//             // die();
//             if (class_exists($handlerClass)) {
//                 $controller = new $handlerClass();
//                 if (method_exists($controller, $handlerMethod)) {
//                     return $controller->$handlerMethod();
//                 }
//             }
//         }
//     }

//     http_response_code(404);
//     echo "404 Not Found";
// }

// handleRequest();
// Gunakan middleware untuk mengonversi respons ke JSON
// Gunakan middleware untuk mengonversi respons ke JSON
$middleware = new JsonResponseMiddleware();
$jsonResponse = $middleware->handle($request, function($request) use ($response) {
    return $response; // Mengembalikan respons asli ke middleware
});

// Kirim respons JSON ke klien
$jsonResponse->send();
