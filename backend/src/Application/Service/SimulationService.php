<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\MatchResultDTO;
use App\Domain\Entity\Game;
use App\Domain\Entity\LeagueTable;
use App\Domain\Repository\LeagueTableRepositoryInterface;
use App\Domain\Repository\TeamRepositoryInterface;

class SimulationService
{
    public function __construct(
        private LeagueTableRepositoryInterface $leagueTableRepository,
        private TeamRepositoryInterface $teamRepository
    ) {
    }

    public function playNextWeek(int $leagueId): array
    {
        $leagueTable = $this->getLeagueTable($leagueId);
        $games = $leagueTable->playWeek();

        // Save all teams to update their statistics
        foreach ($leagueTable->getTeams() as $team) {
            $this->teamRepository->save($team);
        }

        $this->leagueTableRepository->save($leagueTable);

        return $this->getMatchResults($games);
    }

    public function playAllRemainingWeeks(int $leagueId): array
    {
        $leagueTable = $this->getLeagueTable($leagueId);
        $weeklyResults = $leagueTable->playAllRemainingWeeks();

        // Save all teams to update their statistics
        foreach ($leagueTable->getTeams() as $team) {
            $this->teamRepository->save($team);
        }

        $this->leagueTableRepository->save($leagueTable);

        return array_map(function ($games) {
            return $this->getMatchResults($games);
        }, $weeklyResults);
    }

    public function updateGameResult(int $leagueId, int $gameId, int $homeGoals, int $awayGoals): void
    {
        $leagueTable = $this->getLeagueTable($leagueId);

        // Find the game and update its result
        $fixtures = $leagueTable->getFixtures();
        foreach ($fixtures as $game) {
            if ($game->getId() === $gameId) {
                $game->setResult($homeGoals, $awayGoals);

                // Save the teams involved in this game
                $this->teamRepository->save($game->getHomeTeam());
                $this->teamRepository->save($game->getAwayTeam());

                break;
            }
        }

        $this->leagueTableRepository->save($leagueTable);
    }

    private function getLeagueTable(int $leagueId): LeagueTable
    {
        $leagueTable = $this->leagueTableRepository->findById($leagueId);

        if (!$leagueTable) {
            throw new \InvalidArgumentException("League with ID $leagueId not found");
        }

        return $leagueTable;
    }

    private function getMatchResults(array $games): array
    {
        return array_map(function (Game $game) {
            $result = $game->getResult();

            return new MatchResultDTO(
                $game->getId(),
                $game->getHomeTeam()->getId(),
                $game->getHomeTeam()->getName(),
                $game->getAwayTeam()->getId(),
                $game->getAwayTeam()->getName(),
                $game->getWeek(),
                $result ? $result->getHomeGoals() : null,
                $result ? $result->getAwayGoals() : null,
                $result ? $result->getScore() : null
            );
        }, $games);
    }
}