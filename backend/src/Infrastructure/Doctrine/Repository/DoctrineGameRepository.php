<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineGameRepository implements GameRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById(int $id): ?Game
    {
        return $this->entityManager->find(Game::class, $id);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(Game::class)->findAll();
    }

    public function findByWeek(int $week): array
    {
        return $this->entityManager->getRepository(Game::class)->findBy(['week' => $week]);
    }

    public function save(Game $game): void
    {
        $this->entityManager->persist($game);
        $this->entityManager->flush();
    }

    public function delete(Game $game): void
    {
        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }
}