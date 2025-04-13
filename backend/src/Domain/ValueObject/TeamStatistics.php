<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class TeamStatistics
{
    private int $played;
    private int $won;
    private int $drawn;
    private int $lost;
    private int $goalsFor;
    private int $goalsAgainst;

    public function __construct(
        int $played = 0,
        int $won = 0,
        int $drawn = 0,
        int $lost = 0,
        int $goalsFor = 0,
        int $goalsAgainst = 0
    ) {
        $this->played = $played;
        $this->won = $won;
        $this->drawn = $drawn;
        $this->lost = $lost;
        $this->goalsFor = $goalsFor;
        $this->goalsAgainst = $goalsAgainst;
    }

    public function getPlayed(): int
    {
        return $this->played;
    }

    public function getWon(): int
    {
        return $this->won;
    }

    public function getDrawn(): int
    {
        return $this->drawn;
    }

    public function getLost(): int
    {
        return $this->lost;
    }

    public function getGoalsFor(): int
    {
        return $this->goalsFor;
    }

    public function getGoalsAgainst(): int
    {
        return $this->goalsAgainst;
    }

    public function update(
        int $played = 0,
        int $won = 0,
        int $drawn = 0,
        int $lost = 0,
        int $goalsFor = 0,
        int $goalsAgainst = 0
    ): self {
        return new self(
            $this->played + $played,
            $this->won + $won,
            $this->drawn + $drawn,
            $this->lost + $lost,
            $this->goalsFor + $goalsFor,
            $this->goalsAgainst + $goalsAgainst
        );
    }
}