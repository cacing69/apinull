<?php

require __DIR__ . '/vendor/autoload.php';

use App\Console\SimpleCommand;
use Symfony\Component\Console\Application;

$application = new Application();



$application->add(new SimpleCommand());

$application->run();
