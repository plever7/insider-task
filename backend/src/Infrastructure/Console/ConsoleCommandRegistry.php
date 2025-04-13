<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use Symfony\Component\Console\Application;

class ConsoleCommandRegistry
{

    public function registerCommands(Application $console): void
    {
        // Load Doctrine migration config
        $config = new \Doctrine\Migrations\Configuration\Migration\PhpFile(__DIR__ . '/../../../config/migrations.php');
        // Get DB connection parameters from your config or env
        $dbParams = require __DIR__ . '/../../../config/migrations-db.php';
        // Setup DB connection
        $connection = \Doctrine\DBAL\DriverManager::getConnection(
            $dbParams
        );

        // Setup DependencyFactory
        $dependencyFactory = \Doctrine\Migrations\DependencyFactory::fromConnection(
            $config,
            new \Doctrine\Migrations\Configuration\Connection\ExistingConnection($connection)
        );

        // Register Doctrine migration commands
        $console->addCommands([
            new \Doctrine\Migrations\Tools\Console\Command\MigrateCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\DiffCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\ExecuteCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\GenerateCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\LatestCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\ListCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\RollupCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\StatusCommand($dependencyFactory),
            new \Doctrine\Migrations\Tools\Console\Command\VersionCommand($dependencyFactory),
        ]);
    }

}