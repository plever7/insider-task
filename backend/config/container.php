<?php

declare(strict_types=1);

use App\Application\Command\RunSimulationCommand;
use App\Application\Service\LeagueService;
use App\Application\Service\SimulationService;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\Repository\LeagueTableRepositoryInterface;
use App\Domain\Repository\TeamRepositoryInterface;
use App\Infrastructure\Controller\SimulationController;
use App\Infrastructure\Doctrine\EntityManagerFactory;
use App\Infrastructure\Doctrine\Repository\DoctrineGameRepository;
use App\Infrastructure\Doctrine\Repository\DoctrineLeagueTableRepository;
use App\Infrastructure\Doctrine\Repository\DoctrineTeamRepository;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // EntityManager
        EntityManagerInterface::class => function (ContainerInterface $container) {
            $factory = new EntityManagerFactory();
            return $factory->create();
        },

        // Repositories
        TeamRepositoryInterface::class => function (ContainerInterface $container) {
            return new DoctrineTeamRepository($container->get(EntityManagerInterface::class));
        },

        GameRepositoryInterface::class => function (ContainerInterface $container) {
            return new DoctrineGameRepository($container->get(EntityManagerInterface::class));
        },

        LeagueTableRepositoryInterface::class => function (ContainerInterface $container) {
            return new DoctrineLeagueTableRepository($container->get(EntityManagerInterface::class));
        },

        // Services
        LeagueService::class => function (ContainerInterface $container) {
            return new LeagueService(
                $container->get(TeamRepositoryInterface::class),
                $container->get(LeagueTableRepositoryInterface::class)
            );
        },

        SimulationService::class => function (ContainerInterface $container) {
            return new SimulationService(
                $container->get(LeagueTableRepositoryInterface::class),
                $container->get(TeamRepositoryInterface::class),
            );
        },

        RunSimulationCommand::class => function (ContainerInterface $container) {
            return new RunSimulationCommand(
                $container->get(LeagueService::class),
                $container->get(SimulationService::class)
            );
        },

        // Controllers
        SimulationController::class => function (ContainerInterface $container) {
            return new SimulationController(
                $container->get(LeagueService::class),
                $container->get(SimulationService::class)
            );
        },
    ]);
};