<?php
namespace App\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'module:create')]
class ModuleCreateCommand extends Command
{
    // Configure the command's name, description, and help text
    protected function configure()
    {
        $this->setDescription('Create new module') // Brief description of the command
            ->setHelp('This command allows you to create a new module') // Additional help information
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the module'); // Adds a required argument 'name'
    }

    // The logic that runs when the command is executed
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name'); // Retrieve the module name from the input argument

        // Output message indicating the start of the module creation process
        $output->writeln("Creating module '{$name}'");

        // Define the target directory path for the module
        $target = implode(DIRECTORY_SEPARATOR, ["src", "Modules", $name]);
        $app_path = __DIR__ . "/../../"; // Path to the root of the application
        $path = $app_path . $target; // Full path for the new module

        // Check if the module already exists
        if (file_exists($path)) {
            // If the module already exists, output an error message and return failure
            $output->writeln("Module '{$name}' already exists");
            return Command::FAILURE;
        }

        // Create the module directory with permissions 0755
        mkdir($path, 0755);

        // Create the subdirectory for handling HTTP requests
        $handlerDir = $path . DIRECTORY_SEPARATOR . "Http";
        mkdir($handlerDir, 0755);

        // Create the subdirectory for validation files
        mkdir($handlerDir . DIRECTORY_SEPARATOR . "Validate", 0755);

        // Create the handler file based on a stub template
        $handlerContent = file_get_contents($app_path . "stubs/handler.stub");
        $handlerContent = str_replace("{moduleName}", $name, $handlerContent);
        file_put_contents($path . DIRECTORY_SEPARATOR . "Http" . DIRECTORY_SEPARATOR . $name . "Handler.php", $handlerContent);

        // Create the route definition file for the module based on a stub template
        $routeContent = file_get_contents($app_path . "stubs/module.route.stub");
        $routeContent = str_replace(["{moduleName}", "{lowerModuleName}"], [$name, strtolower($name)], $routeContent);
        file_put_contents($path . DIRECTORY_SEPARATOR . strtolower($name) . ".routes.yaml", $routeContent);

        return Command::SUCCESS; // Return success if the module was created successfully
    }
}
