<?php

namespace App\Core;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    private static $capsule;

    public static function getInstance()
    {
        if (self::$capsule === null) {
            self::$capsule = new Capsule();

            self::$capsule->addConnection([
                    'driver' => 'mysql', // Atau 'sqlite', 'pgsql', dll.
                    'host' => '127.0.0.1',
                    'database' => 'db_apinull',
                    'username' => 'root',
                    'password' => 'cacing.mysql',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
            ]);

            // Menginisialisasi Eloquent ORM
            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
        }

        return self::$capsule;
    }
}
