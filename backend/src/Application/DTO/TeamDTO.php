<?php

declare(strict_types=1);

namespace App\Application\DTO;

readonly class TeamDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public int|string $strength,
        public int $points,
        public int $played,
        public int $won,
        public int $drawn,
        public int $lost,
        public int $goalsFor,
        public int $goalsAgainst,
        public int $goalDifference
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'strength' => $this->strength,
            'points' => $this->points,
            'played' => $this->played,
            'won' => $this->won,
            'drawn' => $this->drawn,
            'lost' => $this->lost,
            'goalsFor' => $this->goalsFor,
            'goalsAgainst' => $this->goalsAgainst,
            'goalDifference' => $this->goalDifference,
        ];
    }
}