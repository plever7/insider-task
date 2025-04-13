<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\LeagueTable;

interface LeagueTableRepositoryInterface
{
    public function findById(int $id): ?LeagueTable;

    public function findCurrent(): ?LeagueTable;

    public function save(LeagueTable $leagueTable): void;

    public function delete(LeagueTable $leagueTable): void;
}