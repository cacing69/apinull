<?php

require __DIR__ . '/vendor/autoload.php';  // Autoloads all dependencies using Composer

use App\Console\ModuleCreateCommand;  // Importing the ModuleCreateCommand class
use App\Console\ExampleCommand;  // Importing the ExampleCommand class
use Symfony\Component\Console\Application;  // Importing the Symfony Console Application class

// Create a new Symfony Console application instance
$application = new Application();

// Add the ExampleCommand to the application
$application->add(new ExampleCommand());  // Registers the ExampleCommand to be available for execution

// Add the ModuleCreateCommand to the application
$application->add(new ModuleCreateCommand());  // Registers the ModuleCreateCommand to be available for execution

// Run the application, which will listen for the command to be executed from the command line
$application->run();  // Starts the console application and handles input and output
