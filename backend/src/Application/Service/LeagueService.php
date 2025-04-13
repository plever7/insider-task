<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\LeagueTableDTO;
use App\Application\DTO\TeamDTO;
use App\Domain\Entity\LeagueTable;
use App\Domain\Entity\Team;
use App\Domain\Repository\LeagueTableRepositoryInterface;
use App\Domain\Repository\TeamRepositoryInterface;
use App\Domain\ValueObject\TeamStrength;

class LeagueService
{
    public function __construct(
        private TeamRepositoryInterface $teamRepository,
        private LeagueTableRepositoryInterface $leagueTableRepository
    ) {
    }

    public function createNewLeague(
        array $teamDTOs
    ): int {
        // Create teams
        $teams = array_map(function (TeamDTO $teamDTO) {
            $team = new Team(
                $teamDTO->name,
                TeamStrength::fromString($teamDTO->strength)
            );
            $this->teamRepository->save($team);
            return $team;
        }, $teamDTOs);

        // Create league table
        $leagueTable = new LeagueTable($teams);
        $this->leagueTableRepository->save($leagueTable);

        return $leagueTable->getId();
    }

    public function getCurrentLeague(): ?LeagueTableDTO
    {
        $leagueTable = $this->leagueTableRepository->findCurrent();

        if (!$leagueTable) {
            return null;
        }

        return $this->convertToDTO($leagueTable);
    }

    public function getLeagueById(
        int $id
    ): ?LeagueTableDTO {
        $leagueTable = $this->leagueTableRepository->findById($id);

        if (!$leagueTable) {
            return null;
        }

        return $this->convertToDTO($leagueTable);
    }

    private function convertToDTO(
        LeagueTable $leagueTable
    ): LeagueTableDTO {
        $standings = $leagueTable->getStandings();

        $teams = array_map(function (Team $team) {
            $stats = $team->getStatistics();

            return new TeamDTO(
                $team->getId(),
                $team->getName(),
                $team->getStrength()->getValue(),
                $team->calculatePoints(),
                $team->getStatisticsPlayed(),  // Use the database field directly
                $team->getStatisticsWon(),     // Use the database field directly
                $team->getStatisticsDrawn(),   // Use the database field directly
                $team->getStatisticsLost(),    // Use the database field directly
                $team->getStatisticsGoalsFor(),// Use the database field directly
                $team->getStatisticsGoalsAgainst(), // Use the database field directly
                $team->getGoalDifference()
            );
        }, $standings);

        return new LeagueTableDTO(
            $leagueTable->getId(),
            $teams,
            $leagueTable->getCurrentWeek(),
            $leagueTable->isCompleted()
        );
    }
}