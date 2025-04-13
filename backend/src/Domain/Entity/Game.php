<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Event\GamePlayedEvent;
use App\Domain\ValueObject\GameResult;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'games')]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'home_team_id', referencedColumnName: 'id', nullable: false)]
    private Team $homeTeam;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'away_team_id', referencedColumnName: 'id', nullable: false)]
    private Team $awayTeam;

    #[ORM\Column(type: 'integer')]
    private int $week;

    #[ORM\Column(name: 'result_home_goals', type: 'integer', nullable: true)]
    private ?int $resultHomeGoals = null;

    #[ORM\Column(name: 'result_away_goals', type: 'integer', nullable: true)]
    private ?int $resultAwayGoals = null;

    #[ORM\ManyToOne(targetEntity: LeagueTable::class, inversedBy: 'fixtures')]
    #[ORM\JoinColumn(name: 'league_table_id', referencedColumnName: 'id', nullable: false)]
    private LeagueTable $leagueTable;

    // This is a transient field (not persisted)
    private array $events = [];

    public function __construct(
        Team $homeTeam,
        Team $awayTeam,
        int $week
    ) {
        $this->homeTeam = $homeTeam;
        $this->awayTeam = $awayTeam;
        $this->week = $week;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    public function getAwayTeam(): Team
    {
        return $this->awayTeam;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    public function getResult(): ?GameResult
    {
        if ($this->resultHomeGoals === null || $this->resultAwayGoals === null) {
            return null;
        }
        return new GameResult($this->resultHomeGoals, $this->resultAwayGoals);
    }

    public function setLeagueTable(LeagueTable $leagueTable): void
    {
        $this->leagueTable = $leagueTable;
    }

    public function getLeagueTable(): LeagueTable
    {
        return $this->leagueTable;
    }

    public function isPlayed(): bool
    {
        return $this->resultHomeGoals !== null && $this->resultAwayGoals !== null;
    }

    public function play(): void
    {
        if ($this->isPlayed()) {
            return;
        }

        $homeTeamStrength = $this->homeTeam->getStrength()->getValue();
        $awayTeamStrength = $this->awayTeam->getStrength()->getValue();

        // Basic algorithm to calculate goals based on team strengths
        $homeAdvantage = 1.3; // Home team has an advantage
        $randomFactor = mt_rand(80, 120) / 100; // Random factor between 0.8 and 1.2

        $homeTeamGoals = (int) round(($homeTeamStrength * $homeAdvantage * $randomFactor) / 20);
        $awayTeamGoals = (int) round(($awayTeamStrength * $randomFactor) / 20);

        $this->setResult($homeTeamGoals, $awayTeamGoals);
    }

    public function setResult(int $homeTeamGoals, int $awayTeamGoals): void
    {
        $this->resultHomeGoals = $homeTeamGoals;
        $this->resultAwayGoals = $awayTeamGoals;
        $result = new GameResult($homeTeamGoals, $awayTeamGoals);

        // Update home team statistics
        $this->homeTeam->updateStatistics(
            1, // played
            $homeTeamGoals > $awayTeamGoals ? 1 : 0, // won
            $homeTeamGoals === $awayTeamGoals ? 1 : 0, // drawn
            $homeTeamGoals < $awayTeamGoals ? 1 : 0, // lost
            $homeTeamGoals, // goalsFor
            $awayTeamGoals // goalsAgainst
        );

        // Update away team statistics
        $this->awayTeam->updateStatistics(
            1, // played
            $awayTeamGoals > $homeTeamGoals ? 1 : 0, // won
            $homeTeamGoals === $awayTeamGoals ? 1 : 0, // drawn
            $awayTeamGoals < $homeTeamGoals ? 1 : 0, // lost
            $awayTeamGoals, // goalsFor
            $homeTeamGoals // goalsAgainst
        );

        $this->addEvent(new GamePlayedEvent($this));
    }

    public function addEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}