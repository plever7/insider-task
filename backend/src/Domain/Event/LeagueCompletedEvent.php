<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Entity\LeagueTable;
use DateTimeImmutable;

final class LeagueCompletedEvent
{
    private LeagueTable $leagueTable;
    private DateTimeImmutable $occurredAt;

    public function __construct(LeagueTable $leagueTable)
    {
        $this->leagueTable = $leagueTable;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getLeagueTable(): LeagueTable
    {
        return $this->leagueTable;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}