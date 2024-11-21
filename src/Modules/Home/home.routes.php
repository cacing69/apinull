<?php

use App\Http\Middlewares\AuthMiddleware;
use App\Http\Router;
use Modules\Home\Http\HomeHandler;

$router = new Router();

$router->get("/", [HomeHandler::class, "index"]);

return $router->take();


// return [
//     "group" => "",
//     // "middleware" => [AuthMiddleware::class],
//     "routes" =>
//     [
//         [
//             'path' => '/',
//             'handler' => [
//                 HomeHandler::class,
//                 'index'
//             ],
//             'methods' => ['GET'],
//             'middleware' => [
//                 // AuthMiddleware::class
//                 // 'auth'
//             ], // Tambahkan middleware jika ada
//         ],
//         [
//             'path' => '/path/{uuid}',
//             'handler' => [
//                 Modules\Home\Http\HomeHandler::class,
//                 'path'
//             ],
//             'methods' => ['GET'],
//         ],
//         [
//             'path' => '/path/{id}',
//             'handler' => [
//                 Modules\Home\Http\HomeHandler::class,
//                 'pathPost'
//             ],
//             'methods' => ['POST'],
//         ],
//         [
//             'path' => '/dump',
//             'handler' => [
//                 Modules\Home\Http\HomeHandler::class,
//                 'dump'
//             ],
//             'methods' => ['GET'],
//         ],
//         [
//             'path' => '/dump',
//             'handler' => [
//                 Modules\Home\Http\HomeHandler::class,
//                 'dumpPost'
//             ],
//             'methods' => ['POST'],
//         ],
//     ]
// ];
