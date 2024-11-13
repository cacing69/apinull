<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__."/../");
$dotenv->safeLoad();

return [
    'dbname' => $_ENV["DB_DATABASE"],
    'user' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'host' => $_ENV["DB_HOST"],
    'driver' => 'pdo_'.$_ENV["DB_CONNECTION"], // Change this based on your database type
];
