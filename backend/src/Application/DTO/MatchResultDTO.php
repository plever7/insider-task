<?php

declare(strict_types=1);

namespace App\Application\DTO;

readonly class MatchResultDTO
{
    public function __construct(
        public int $id,
        public int $homeTeamId,
        public string $homeTeamName,
        public int $awayTeamId,
        public string $awayTeamName,
        public int $week,
        public int $homeGoals,
        public int $awayGoals,
        public string $score
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'homeTeamId' => $this->homeTeamId,
            'homeTeamName' => $this->homeTeamName,
            'awayTeamId' => $this->awayTeamId,
            'awayTeamName' => $this->awayTeamName,
            'week' => $this->week,
            'homeGoals' => $this->homeGoals,
            'awayGoals' => $this->awayGoals,
            'score' => $this->score,
            'isPlayed' => $this->score !== null,
        ];
    }
}