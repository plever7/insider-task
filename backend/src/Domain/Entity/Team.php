<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\TeamStatistics;
use App\Domain\ValueObject\TeamStrength;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'teams')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'strength_value', type: 'integer')]
    private int $strengthValue;

    #[ORM\Column(name: 'statistics_played', type: 'integer', options: ['default' => 0])]
    private int $statisticsPlayed = 0;

    #[ORM\Column(name: 'statistics_won', type: 'integer', options: ['default' => 0])]
    private int $statisticsWon = 0;

    #[ORM\Column(name: 'statistics_drawn', type: 'integer', options: ['default' => 0])]
    private int $statisticsDrawn = 0;

    #[ORM\Column(name: 'statistics_lost', type: 'integer', options: ['default' => 0])]
    private int $statisticsLost = 0;

    #[ORM\Column(name: 'statistics_goals_for', type: 'integer', options: ['default' => 0])]
    private int $statisticsGoalsFor = 0;

    #[ORM\Column(name: 'statistics_goals_against', type: 'integer', options: ['default' => 0])]
    private int $statisticsGoalsAgainst = 0;

    // These are now reconstructed from database fields
    private TeamStrength $strength;
    private TeamStatistics $statistics;

    public function __construct(
        string $name,
        TeamStrength $strength
    ) {
        $this->name = $name;
        $this->strength = $strength;
        $this->strengthValue = $strength->getValue();
        $this->statistics = new TeamStatistics();
    }

    // Called after entity load from database
    #[ORM\PostLoad]
    public function initializeValueObjects(): void
    {
        $this->strength = TeamStrength::fromValue($this->strengthValue);
        $this->statistics = new TeamStatistics(
            $this->statisticsPlayed,
            $this->statisticsWon,
            $this->statisticsDrawn,
            $this->statisticsLost,
            $this->statisticsGoalsFor,
            $this->statisticsGoalsAgainst
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStrength(): TeamStrength
    {
        if (!isset($this->strength)) {
            $this->strength = TeamStrength::fromValue($this->strengthValue);
        }
        return $this->strength;
    }

    public function getStatistics(): TeamStatistics
    {
        if (!isset($this->statistics)) {
            $this->statistics = new TeamStatistics();
        }
        return $this->statistics;
    }

    public function updateStatistics(
        int $played = 0,
        int $won = 0,
        int $drawn = 0,
        int $lost = 0,
        int $goalsFor = 0,
        int $goalsAgainst = 0
    ): void {
        // First ensure $statistics is initialized
        if (!isset($this->statistics)) {
            $this->statistics = new TeamStatistics(
                $this->statisticsPlayed,
                $this->statisticsWon,
                $this->statisticsDrawn,
                $this->statisticsLost,
                $this->statisticsGoalsFor,
                $this->statisticsGoalsAgainst
            );
        }

        $this->statistics = $this->statistics->update(
            $played,
            $won,
            $drawn,
            $lost,
            $goalsFor,
            $goalsAgainst
        );

        // Update database columns
        $this->statisticsPlayed += $played;
        $this->statisticsWon += $won;
        $this->statisticsDrawn += $drawn;
        $this->statisticsLost += $lost;
        $this->statisticsGoalsFor += $goalsFor;
        $this->statisticsGoalsAgainst += $goalsAgainst;
    }

    public function calculatePoints(): int
    {
        return $this->statisticsWon * 3 + $this->statisticsDrawn;
    }

    public function getGoalDifference(): int
    {
        return $this->statisticsGoalsFor - $this->statisticsGoalsAgainst;
    }

    public function getStrengthValue(): int
    {
        return $this->strengthValue;
    }

    public function getStatisticsPlayed(): int
    {
        return $this->statisticsPlayed;
    }

    public function getStatisticsWon(): int
    {
        return $this->statisticsWon;
    }

    public function getStatisticsDrawn(): int
    {
        return $this->statisticsDrawn;
    }

    public function getStatisticsLost(): int
    {
        return $this->statisticsLost;
    }

    public function getStatisticsGoalsFor(): int
    {
        return $this->statisticsGoalsFor;
    }

    public function getStatisticsGoalsAgainst(): int
    {
        return $this->statisticsGoalsAgainst;
    }
}