<?php

namespace App\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'module:create')]
class CreateModuleCommand extends Command
{
    // protected static $defaultName = 'app:simple';

    protected function configure()
    {
        $this->setDescription('This is a test command')
            ->setHelp('This command allows you to create a user...')
            // $this->setDescription('Creates a new user')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the user')
            ->addArgument('class', InputArgument::REQUIRED, 'The class of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $class = $input->getArgument('class');

        // Logika untuk membuat user baru
        // Misalnya, memanggil UserService untuk menambah user ke database

        $output->writeln("User '{$name}' created successfully!");
        $output->writeln("User class '{$class}' created successfully!");
        $output->writeln('This is a test command.');

        return Command::SUCCESS;
    }
}
