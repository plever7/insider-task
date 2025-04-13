<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\LeagueTable;
use App\Domain\Repository\LeagueTableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineLeagueTableRepository implements LeagueTableRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById(int $id): ?LeagueTable
    {
        return $this->entityManager->find(LeagueTable::class, $id);
    }

    public function findCurrent(): ?LeagueTable
    {
        $repository = $this->entityManager->getRepository(LeagueTable::class);

        // Get the most recently created league
        return $repository->findOneBy(
            ['isCompleted' => false],
            ['id' => 'DESC']
        ) ?? $repository->findOneBy([], ['id' => 'DESC']);
    }

    public function save(LeagueTable $leagueTable): void
    {
        $this->entityManager->persist($leagueTable);

        // Also persist all fixtures/games
        foreach ($leagueTable->getFixtures() as $game) {
            $this->entityManager->persist($game);
        }

        $this->entityManager->flush();
    }

    public function delete(LeagueTable $leagueTable): void
    {
        $this->entityManager->remove($leagueTable);
        $this->entityManager->flush();
    }
}