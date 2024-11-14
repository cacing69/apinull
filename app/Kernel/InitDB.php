<?php

namespace App\Kernel;

use Illuminate\Database\Capsule\Manager;

class InitDB
{
    private static $manager;

    public static function getInstance()
    {

        if (self::$manager === null) {
            self::$manager = new Manager();

            self::$manager->addConnection([
                    'driver' => $_ENV["DB_CONNECTION"],
                    'host' => $_ENV["DB_HOST"],
                    'database' => $_ENV["DB_DATABASE"],
                    'username' => $_ENV['DB_USERNAME'],
                    'password' => $_ENV['DB_PASSWORD'],
                    'port' => 5432,
                    'charset' => 'utf8',
                    'prefix' => '',
                    'schema'   => 'public',
            ]);

            // Menginisialisasi Eloquent ORM
            self::$manager->setAsGlobal();
            self::$manager->bootEloquent();
        }

        return self::$manager;
    }
}
