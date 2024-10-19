<?php
// config/database.php
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// Mengatur koneksi ke database
$capsule->addConnection([
    'driver' => 'sqlite', // Atau 'sqlite', 'pgsql', dll.
    'host' => '127.0.0.1',
    'database' => 'nama_database',
    'username' => 'root',
    'password' => 'cacing.mysql',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

// Set global untuk Eloquent
$capsule->setAsGlobal();
$capsule->bootEloquent();
