<?php

namespace App\Kernel;

use Illuminate\Database\Capsule\Manager as Capsule;

class DB
{
    private static $capsule;

    public static function getInstance()
    {
        if (self::$capsule === null) {
            self::$capsule = new Capsule();

            self::$capsule->addConnection([
                    'driver' => 'pgsql', // Atau 'sqlite', 'pgsql', 'mysql'.
                    'host' => 'us-east-1.sql.xata.sh',
                    'database' => 'apinull',
                    'username' => '3aadso',
                    // 'password' => 'cacing.mysql',
                    'password' => 'xau_bMQYUHFhI4vmowi9q88GRdpjkFJuJ2fB1',
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

    public static function table($table)
    {
        return Capsule::table($table);
    }

    public static function getAllUsers()
    {
        return self::table('users')->get();
    }

    public static function insert($table, array $data)
    {
        return self::table($table)->insert($data);
    }

    // Update dengan parameter array
    public static function update($table, array $data, array $conditions)
    {
        $query = self::table($table);
        foreach ($conditions as $key => $value) {
            $query->where($key, $value);
        }
        return $query->update($data);
    }

    // Delete dengan parameter array
    public static function delete($table, array $conditions)
    {
        $query = self::table($table);
        foreach ($conditions as $key => $value) {
            $query->where($key, $value);
        }
        return $query->delete();
    }
}
