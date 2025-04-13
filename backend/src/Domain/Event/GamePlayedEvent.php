<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Entity\Game;
use DateTimeImmutable;

final class GamePlayedEvent
{
    private Game $game;
    private DateTimeImmutable $occurredAt;

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}