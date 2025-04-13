<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class GameResult
{
    private int $homeGoals;
    private int $awayGoals;

    public function __construct(int $homeGoals, int $awayGoals)
    {
        $this->homeGoals = $homeGoals;
        $this->awayGoals = $awayGoals;
    }

    public function getHomeGoals(): int
    {
        return $this->homeGoals;
    }

    public function getAwayGoals(): int
    {
        return $this->awayGoals;
    }

    public function isHomeWin(): bool
    {
        return $this->homeGoals > $this->awayGoals;
    }

    public function isAwayWin(): bool
    {
        return $this->homeGoals < $this->awayGoals;
    }

    public function isDraw(): bool
    {
        return $this->homeGoals === $this->awayGoals;
    }

    public function getWinner(): string
    {
        if ($this->isDraw()) {
            return 'draw';
        }

        return $this->isHomeWin() ? 'home' : 'away';
    }

    public function getScore(): string
    {
        return sprintf('%d - %d', $this->homeGoals, $this->awayGoals);
    }
}