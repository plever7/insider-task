<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Team;

interface TeamRepositoryInterface
{
    public function findById(int $id): ?Team;

    public function findAll(): array;

    public function save(Team $team): void;

    public function delete(Team $team): void;
}