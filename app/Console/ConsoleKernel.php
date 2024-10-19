<?php

namespace App\Console;

use Symfony\Component\Console\Application;

class ConsoleKernel
{
    private $app;

    public function __construct()
    {
        $this->app = new Application();
    }

    public function registerCommands()
    {
        $this->app->add(new SimpleCommand()); // Pastikan command ini terdaftar di sini
    }

    public function run()
    {
        $this->registerCommands();
        $this->app->run();
    }
}
