<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Event\LeagueCompletedEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'league_tables')]
class LeagueTable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToMany(targetEntity: Team::class)]
    #[ORM\JoinTable(
        name: 'league_teams',
        joinColumns: [new ORM\JoinColumn(name: 'league_table_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    )]
    private Collection $teams;

    #[ORM\Column(name: 'current_week', type: 'integer', options: ['default' => 0])]
    private int $currentWeek = 0;

    #[ORM\OneToMany(mappedBy: 'leagueTable', targetEntity: Game::class, cascade: ['persist', 'remove'])]
    private Collection $fixtures;

    #[ORM\Column(name: 'is_completed', type: 'boolean', options: ['default' => false])]
    private bool $isCompleted = false;

    #[ORM\Column(name: 'created_at', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $createdAt;

    // This is a transient field (not persisted)
    private array $events = [];

    public function __construct(array $teams)
    {
        $this->teams = new ArrayCollection();
        $this->fixtures = new ArrayCollection();
        $this->createdAt = new \DateTime();

        foreach ($teams as $team) {
            $this->teams->add($team);
        }

        $this->generateFixtures();
    }

    // Rest of your existing methods...
    // You'll need to modify them to work with Collections instead of arrays

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function getFixtures(): Collection
    {
        return $this->fixtures;
    }

    public function getFixturesByWeek(int $week): array
    {
        return $this->fixtures->filter(function (Game $game) use ($week) {
            return $game->getWeek() === $week;
        })->toArray();
    }

    private function generateFixtures(): void
    {
        $teams = $this->teams->toArray();
        $teamCount = count($teams);

        // For a standard round-robin tournament with even number of teams
        // We need to implement a proper round-robin algorithm

        // First half of the season
        for ($round = 0; $round < $teamCount - 1; $round++) {
            // In each round, pair the teams correctly
            for ($match = 0; $match < $teamCount / 2; $match++) {
                $home = ($round + $match) % ($teamCount - 1);
                $away = ($teamCount - 1 - $match + $round) % ($teamCount - 1);

                // Last team stays fixed, others rotate
                if ($match == 0) {
                    $away = $teamCount - 1;
                }

                // Create the game and ensure teams aren't playing themselves
                if ($home != $away) {
                    $game = new Game($teams[$home], $teams[$away], $round + 1);
                    $game->setLeagueTable($this);
                    $this->fixtures->add($game);
                }
            }
        }

        // Second half - reverse home/away
        $firstHalfMaxWeek = $teamCount - 1;

        for ($week = 1; $week <= $firstHalfMaxWeek; $week++) {
            $firstHalfFixtures = $this->getFixturesByWeek($week);

            foreach ($firstHalfFixtures as $originalGame) {
                $game = new Game(
                    $originalGame->getAwayTeam(),
                    $originalGame->getHomeTeam(),
                    $week + $firstHalfMaxWeek
                );
                $game->setLeagueTable($this);
                $this->fixtures->add($game);
            }
        }
    }

    public function getStandings(): array
    {
        $standings = $this->teams->toArray();

        // Sort by points, goal difference, goals scored
        usort($standings, function (Team $a, Team $b) {
            $pointsA = $a->calculatePoints();
            $pointsB = $b->calculatePoints();

            if ($pointsA !== $pointsB) {
                return $pointsB - $pointsA; // Descending by points
            }

            $goalDiffA = $a->getGoalDifference();
            $goalDiffB = $b->getGoalDifference();

            if ($goalDiffA !== $goalDiffB) {
                return $goalDiffB - $goalDiffA; // Descending by goal difference
            }

            return $b->getStatistics()->getGoalsFor() - $a->getStatistics()->getGoalsFor(
                ); // Descending by goals scored
        });

        return $standings;
    }

    // Don't forget to add other necessary methods like getters/setters
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCurrentWeek(): int
    {
        return $this->currentWeek;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function playAllRemainingWeeks(): array
    {
        $results = [];
        $totalWeeks = $this->calculateTotalWeeks();

        while ($this->currentWeek < $totalWeeks) {
            $results[$this->currentWeek + 1] = $this->playWeek();
        }

        return $results;
    }

    private function calculateTotalWeeks(): int
    {
        $teamCount = $this->teams->count();
        return ($teamCount - 1) * 2;
    }

    public function playWeek(): array
    {
        $this->currentWeek++;
        $games = $this->getFixturesByWeek($this->currentWeek);

        foreach ($games as $game) {
            $game->play();
        }

        // Check if league is completed
        $totalWeeks = $this->calculateTotalWeeks();
        if ($this->currentWeek >= $totalWeeks) {
            $this->isCompleted = true;
            $this->addEvent(new LeagueCompletedEvent($this));
        }

        return $games;
    }

    /**
     * Add an event to the transient events array
     */
    public function addEvent(object $event): void
    {
        $this->events[] = $event;
    }

    /**
     * Get all recorded events
     *
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}