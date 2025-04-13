<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class EntityManagerFactory
{
    public function create(): EntityManagerInterface
    {
        // Path to where your entities are located - adjust this path if needed
        $paths = [__DIR__ . '/../../Domain/Entity'];
        $isDevMode = $_ENV['APP_ENV'] === 'dev';

        // Use attribute metadata driver instead of XML
        $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);

        // Configure cache
        $cache = new ArrayAdapter();
        $config->setMetadataCache($cache);
        $config->setQueryCache($cache);
        $config->setResultCache($cache);

        $connection = DriverManager::getConnection([
            'driver' => $_ENV['DB_DRIVER'],
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'dbname' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'charset' => 'utf8mb4',
        ], $config);

        return new EntityManager($connection, $config);
    }
}