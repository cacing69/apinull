<?php
require_once __DIR__ . '/vendor/autoload.php'; // Autoload dependencies using Composer

use App\Kernel\InitDB; // Import InitDB class to initialize the database
use App\Http\RouterDispatcher; // Import Router class for routing HTTP requests
use Dotenv\Dotenv; // Import Dotenv class to load environment variables
use Illuminate\Http\Request; // Import Request class from Laravel's HTTP package

define("APINULL_PATH", __DIR__); // Define the constant 'APINULL_PATH' as the base directory of the project

// Create a new container instance for dependency injection
$container = new \App\Kernel\Container();

// Load the configuration file for container bindings and bind repositories to the container
$repositories = require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'containers.php';
foreach ($repositories as $abstract => $concrete) {
    $container->bind($abstract, $concrete); // Bind the abstract class to the concrete implementation
}

// Check if the current HTTP host matches the pattern for localhost with a port (e.g., localhost:8080)
if (preg_match('/^(localhost|127\.0\.0\.1):\d{4}$/', $_SERVER['HTTP_HOST'])) {
    // If the host is a local development server, initialize Dotenv to load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad(); // Load .env file
}

// Initialize the database using Singleton pattern
InitDB::getInstance(); // This will set up the database connection and initialize Eloquent ORM

// Create a Request object from the global HTTP request data
$request = Request::capture(); // Capture the incoming HTTP request

// Initialize the Router and load routes from the routes.yaml file
$router = new RouterDispatcher(__DIR__ . '/routes.yaml', $container);

// Dispatch the captured request to find the appropriate handler and generate a response
$response = $router->dispatch($request);

// Send the generated response to the client
$response->send();
