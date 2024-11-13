<?php

namespace App\Kernel;

use Illuminate\Database\Capsule\Manager as Capsule;

class InitDB
{
    private static $capsule;

    public static function getInstance()
    {
        if (self::$capsule === null) {
            self::$capsule = new Capsule();

            self::$capsule->addConnection([
                    // 'driver' => 'pgsql', // Atau 'sqlite', 'pgsql', 'mysql'.
                    // 'host' => 'us-east-1.sql.xata.sh',
                    // 'database' => 'apinull',
                    // 'username' => '3aadso',
                    // 'password' => 'xau_bMQYUHFhI4vmowi9q88GRdpjkFJuJ2fB1',
                    // 'charset' => 'utf8',
                    // 'collation' => 'utf8_unicode_ci',
                    // 'prefix' => '',
                    // ===
                    'driver' => 'pgsql', // Atau 'sqlite', 'pgsql', 'mysql'.
                    'host' => 'localhost',
                    'database' => 'apinull',
                    'username' => 'develop',
                    'password' => '',
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
