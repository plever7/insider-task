<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
        'version_column_name' => 'version',
        'version_column_length' => 1024,
        'executed_at_column_name' => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],
    'migrations_paths' => [
        'DoctrineMigrations' => __DIR__ . '/../migrations', // Adjust path to your migrations directory
    ],
    'all_or_nothing' => true,
    'check_database_platform' => true,
    'organize_migrations' => 'none',

];