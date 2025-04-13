<?php

declare(strict_types=1);

namespace App\Application\DTO;

readonly class LeagueTableDTO
{
    /**
     * @param int $id
     * @param TeamDTO[] $teams
     * @param int $currentWeek
     * @param bool $isCompleted
     */
    public function __construct(
        public int $id,
        public array $teams,
        public int $currentWeek,
        public bool $isCompleted
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'teams' => array_map(fn(TeamDTO $team) => $team->toArray(), $this->teams),
            'currentWeek' => $this->currentWeek,
            'isCompleted' => $this->isCompleted,
        ];
    }
}