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
    protected function configure()
    {
        $this->setDescription('Create new module')
            ->setHelp('This command allows you to create a new module')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the module');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $output->writeln("Creating module '{$name}'");

        $target = implode(DIRECTORY_SEPARATOR, ["src", "Modules", $name]);
        $app_path = __DIR__ . "/../../";
        $path = $app_path . $target;

        // check path
        if (file_exists($path)) {
            $output->writeln("Module '{$name}' already exist");
            return Command::FAILURE;
        }

        // create dir
        mkdir($path, 0755);

        $handlerDir = $path . DIRECTORY_SEPARATOR . "Http";
        // create sub dir
        mkdir($handlerDir, 0755);

        // validate
        mkdir($handlerDir.DIRECTORY_SEPARATOR."Validate", 0755);


        // create handler
        $handlerContent = file_get_contents($app_path."stubs/handler.stub");
        $handlerContent = str_replace("{moduleName}", $name, $handlerContent);
        file_put_contents($path.DIRECTORY_SEPARATOR."Http".DIRECTORY_SEPARATOR.$name."Handler.php", $handlerContent);

        // create route
        $routeContent = file_get_contents($app_path."stubs/module.route.stub");
        $routeContent = str_replace(["{moduleName}", "{lowerModuleName}"], [$name, strtolower($name)], $routeContent);
        file_put_contents($path.DIRECTORY_SEPARATOR.strtolower($name).".routes.yaml", $routeContent);


        //append to route root
        $pathRootRoute = $app_path."routes.yaml";
        $rootRoute = file_get_contents($pathRootRoute);
        $checkPatternRoute = '/src\/Modules\/'.$name.'\/' . strtolower($name) . '\.routes\.yaml/';

        if(!preg_match($checkPatternRoute, $rootRoute)) {
            $newRootRoute ="{$rootRoute}  - { resource: src/Modules/{$name}/".strtolower($name).".routes.yaml }\n";
        }

        file_put_contents(trim($pathRootRoute), $newRootRoute);

        return Command::SUCCESS;
    }
}
