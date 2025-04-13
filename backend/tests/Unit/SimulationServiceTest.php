<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Application\DTO\MatchResultDTO;
use App\Application\Service\SimulationService;
use App\Domain\Entity\Game;
use App\Domain\Entity\LeagueTable;
use App\Domain\Entity\Team;
use App\Domain\Repository\LeagueTableRepositoryInterface;
use App\Domain\Repository\TeamRepositoryInterface;
use App\Domain\ValueObject\TeamStrength;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SimulationServiceTest extends TestCase
{
    private SimulationService $simulationService;
    private LeagueTableRepositoryInterface|MockObject $leagueTableRepository;
    private TeamRepositoryInterface|MockObject $teamRepository;

    protected function setUp(): void
    {
        $this->leagueTableRepository = $this->createMock(LeagueTableRepositoryInterface::class);
        $this->teamRepository = $this->createMock(TeamRepositoryInterface::class);

        $this->simulationService = new SimulationService(
            $this->leagueTableRepository,
            $this->teamRepository
        );
    }

    public function testPlayNextWeek(): void
    {
        // Create test teams
        $team1 = new Team('Team 1', TeamStrength::fromValue(70));
        $team2 = new Team('Team 2', TeamStrength::fromValue(60));
        $team3 = new Team('Team 3', TeamStrength::fromValue(80));
        $team4 = new Team('Team 4', TeamStrength::fromValue(75));

        // Set reflection to set team IDs
        $reflectionTeam = new \ReflectionClass(Team::class);
        $teamIdProperty = $reflectionTeam->getProperty('id');
        $teamIdProperty->setAccessible(true);
        $teamIdProperty->setValue($team1, 1);
        $teamIdProperty->setValue($team2, 2);
        $teamIdProperty->setValue($team3, 3);
        $teamIdProperty->setValue($team4, 4);

        $teams = [$team1, $team2, $team3, $team4];

        // Create a league table with the teams
        $leagueTable = new LeagueTable($teams);

        // Set reflection to set league ID
        $reflectionLeague = new \ReflectionClass(LeagueTable::class);
        $leagueIdProperty = $reflectionLeague->getProperty('id');
        $leagueIdProperty->setAccessible(true);
        $leagueIdProperty->setValue($leagueTable, 1);

        // Set IDs for all Game objects in the league's fixtures
        $reflectionGame = new \ReflectionClass(Game::class);
        $gameIdProperty = $reflectionGame->getProperty('id');
        $gameIdProperty->setAccessible(true);

        // Assume LeagueTable has a method or property to access fixtures (adjust as needed)
        $fixturesProperty = $reflectionLeague->getProperty('fixtures'); // Adjust if different
        $fixturesProperty->setAccessible(true);
        $fixtures = $fixturesProperty->getValue($leagueTable);

        foreach ($fixtures as $index => $game) {
            $gameIdProperty->setValue($game, $index + 1); // Set unique IDs starting from 1
            // Also set leagueTable reference for each game
            $setLeagueTableMethod = $reflectionGame->getMethod('setLeagueTable');
            $setLeagueTableMethod->invoke($game, $leagueTable);
        }

        // Mock repository methods
        $this->leagueTableRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($leagueTable);

        $this->leagueTableRepository->expects($this->once())
            ->method('save')
            ->with($leagueTable);

        $this->teamRepository->expects($this->exactly(4))
            ->method('save')
            ->with($this->isInstanceOf(Team::class));

        // Execute the method to test
        $results = $this->simulationService->playNextWeek(1);

        // Assertions
        $this->assertCount(2, $results); // Should have 2 games in week 1
        $this->assertContainsOnlyInstancesOf(MatchResultDTO::class, $results);

        // Check that week was incremented
        $this->assertEquals(1, $leagueTable->getCurrentWeek());
    }

    public function testPlayAllRemainingWeeks(): void
    {
        // Create test teams
        $team1 = new Team('Team 1', TeamStrength::fromValue(70));
        $team2 = new Team('Team 2', TeamStrength::fromValue(60));
        $team3 = new Team('Team 3', TeamStrength::fromValue(80));
        $team4 = new Team('Team 4', TeamStrength::fromValue(75));

        // Set reflection to set team IDs
        $reflectionTeam = new \ReflectionClass(Team::class);
        $teamIdProperty = $reflectionTeam->getProperty('id');
        $teamIdProperty->setAccessible(true);
        $teamIdProperty->setValue($team1, 1);
        $teamIdProperty->setValue($team2, 2);
        $teamIdProperty->setValue($team3, 3);
        $teamIdProperty->setValue($team4, 4);

        $teams = [$team1, $team2, $team3, $team4];

        // Create a league table with the teams
        $leagueTable = new LeagueTable($teams);

        // Set reflection to set league ID
        $reflectionLeague = new \ReflectionClass(LeagueTable::class);
        $leagueIdProperty = $reflectionLeague->getProperty('id');
        $leagueIdProperty->setAccessible(true);
        $leagueIdProperty->setValue($leagueTable, 1);

        // Set IDs for all Game objects in the league's fixtures
        $reflectionGame = new \ReflectionClass(Game::class);
        $gameIdProperty = $reflectionGame->getProperty('id');
        $gameIdProperty->setAccessible(true);

        // Assume LeagueTable has a method or property to access fixtures (adjust as needed)
        $fixturesProperty = $reflectionLeague->getProperty('fixtures'); // Adjust if different
        $fixturesProperty->setAccessible(true);
        $fixtures = $fixturesProperty->getValue($leagueTable);

        foreach ($fixtures as $index => $game) {
            $gameIdProperty->setValue($game, $index + 1); // Set unique IDs starting from 1
            // Also set leagueTable reference for each game
            $setLeagueTableMethod = $reflectionGame->getMethod('setLeagueTable');
            $setLeagueTableMethod->invoke($game, $leagueTable);
        }

        // Mock repository methods
        $this->leagueTableRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($leagueTable);

        $this->leagueTableRepository->expects($this->once())
            ->method('save')
            ->with($leagueTable);

        $this->teamRepository->expects($this->exactly(4))
            ->method('save')
            ->with($this->isInstanceOf(Team::class));

        // Execute the method to test
        $results = $this->simulationService->playAllRemainingWeeks(1);

        // Assertions
        $this->assertIsArray($results);
        $this->assertCount(6, $results); // 4 teams means 6 weeks (3*2)

        // Check each week's results
        foreach ($results as $weekNumber => $weekResults) {
            $this->assertIsArray($weekResults);
            $this->assertCount(2, $weekResults); // Each week has 2 games with 4 teams
            $this->assertContainsOnlyInstancesOf(MatchResultDTO::class, $weekResults);
        }

        // League should be completed
        $this->assertTrue($leagueTable->isCompleted());
        $this->assertEquals(6, $leagueTable->getCurrentWeek());
    }

    public function testPlayNextWeekThrowsExceptionForInvalidLeague(): void
    {
        // Mock repository to return null for non-existent league
        $this->leagueTableRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        // Set expectation for exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('League with ID 999 not found');

        // Call method with invalid ID
        $this->simulationService->playNextWeek(999);
    }

    public function testPlayAllRemainingWeeksThrowsExceptionForInvalidLeague(): void
    {
        // Mock repository to return null for non-existent league
        $this->leagueTableRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        // Set expectation for exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('League with ID 999 not found');

        // Call method with invalid ID
        $this->simulationService->playAllRemainingWeeks(999);
    }
}