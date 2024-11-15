<?php

namespace App\Kernel;

use Illuminate\Database\Capsule\Manager;

class InitDB
{
    // Static property to store the database manager instance
    private static $manager;

    /**
     * Returns the singleton instance of the database manager.
     * If the instance does not exist, it will create and configure a new one.
     *
     * @return Manager
     */
    public static function getInstance()
    {
        // Check if the instance is already created
        if (self::$manager === null) {
            // Create a new Manager instance if it doesn't exist
            self::$manager = new Manager();

            // Configure the database connection using environment variables
            self::$manager->addConnection([
                'driver' => $_ENV["DB_CONNECTION"], // Database connection type (e.g., mysql, pgsql)
                'host' => $_ENV["DB_HOST"], // Database host (e.g., localhost, remote server)
                'database' => $_ENV["DB_DATABASE"], // Database name
                'username' => $_ENV['DB_USERNAME'], // Database username
                'password' => $_ENV['DB_PASSWORD'], // Database password
                'port' => 5432, // Database port (default PostgreSQL port)
                'charset' => 'utf8', // Character set for the connection
                'prefix' => '', // Table prefix (empty string by default)
                'schema' => 'public', // Database schema (used primarily with PostgreSQL)
            ]);

            // Initialize the Eloquent ORM globally
            self::$manager->setAsGlobal(); // Makes the database connection globally available
            self::$manager->bootEloquent(); // Boot Eloquent ORM
        }

        // Return the existing or newly created Manager instance
        return self::$manager;
    }
}
