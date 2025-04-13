<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Game;

interface GameRepositoryInterface
{
    public function findById(int $id): ?Game;

    public function findAll(): array;

    public function findByWeek(int $week): array;

    public function save(Game $game): void;

    public function delete(Game $game): void;
}