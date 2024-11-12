<?php

return [
    'migrations_paths' => [
        'App\Migrations' => './migrations',
    ],
    'all_or_nothing' => true, // Jika terjadi error, rollback seluruhnya
    'check_database_platform' => true, // Memastikan platform DB konsisten
];
