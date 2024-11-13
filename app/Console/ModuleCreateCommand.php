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
            ->setHelp('This command allows you to create a new module...')
            // $this->setDescription('Creates a new user')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the module');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $output->writeln("Creating module '{$name}'");

        $target = implode(DIRECTORY_SEPARATOR, ["src", "Modules", $name]);

        $path = __DIR__ . "/../../" . $target;

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

        // create handler
        $handlerContent =
"<?php
    namespace Modules\\{$name}\\Http;

    use App\\Http\\BaseHandler;
    use Illuminate\\Http\Request;
    use Illuminate\\Database\\Capsule\\Manager as DB;

    class {$name}Handler extends BaseHandler
    {
        public function index(Request \$request)
        {
            \$data = [
                \"data\" => null,
                \"meta\" => null,
                \"error\" => null
            ];
            return \$data;
        }
    }
";

        file_put_contents($path.DIRECTORY_SEPARATOR."Http".DIRECTORY_SEPARATOR.$name."Handler.php", $handlerContent);


        // create route
        $routeContent =
"
module: {$name}
routes:
  - path: /".strtolower($name)."
    handler: Modules\\{$name}\\Http\\{$name}Handler::index
    methods: [GET]
";
        file_put_contents($path.DIRECTORY_SEPARATOR.strtolower($name).".routes.yaml", $routeContent);


        //append to route root
        $pathRootRoute = __DIR__ . "/../../routes.yaml";
        $rootRoute = file_get_contents($pathRootRoute);

        $newRootRoute =
"{$rootRoute}  - { resource: src/Modules/{$name}/".strtolower($name).".routes.yaml }
";

        file_put_contents(trim($pathRootRoute), $newRootRoute);


        return Command::SUCCESS;
    }
}
