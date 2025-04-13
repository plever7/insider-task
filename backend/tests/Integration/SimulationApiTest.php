<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Domain\Entity\Game;
use App\Domain\Entity\LeagueTable;
use App\Domain\Entity\Team;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\Repository\LeagueTableRepositoryInterface;
use App\Domain\Repository\TeamRepositoryInterface;
use App\Domain\ValueObject\TeamStrength;
use App\Infrastructure\Controller\SimulationController;
use Doctrine\ORM\EntityManagerInterface;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class SimulationApiTest extends TestCase
{
    private App $app;
    private Container $container;
    private LeagueTableRepositoryInterface $leagueTableRepository;
    private TeamRepositoryInterface $teamRepository;
    private GameRepositoryInterface $gameRepository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // Create container with mocks
        $this->container = new Container();

        // Set up entity manager mock
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->container->set(EntityManagerInterface::class, $this->entityManager);

        // Set up repository mocks
        $this->leagueTableRepository = $this->createMock(LeagueTableRepositoryInterface::class);
        $this->teamRepository = $this->createMock(TeamRepositoryInterface::class);
        $this->gameRepository = $this->createMock(GameRepositoryInterface::class);

        $this->container->set(LeagueTableRepositoryInterface::class, $this->leagueTableRepository);
        $this->container->set(TeamRepositoryInterface::class, $this->teamRepository);
        $this->container->set(GameRepositoryInterface::class, $this->gameRepository);

        // Create app with container
        $this->app = AppFactory::createFromContainer($this->container);

        // Add routes
        $this->configureRoutes($this->app);

        // Add middleware for parsing request body
        $this->app->addBodyParsingMiddleware();
    }

    private function configureRoutes(App $app): void
    {
        $app->group('/api', function (\Slim\Routing\RouteCollectorProxy $group) {
            // League Management
            $group->post('/leagues', [SimulationController::class, 'initializeLeague']);
            $group->get('/leagues/current', [SimulationController::class, 'getCurrentLeague']);

            // Simulation Control
            $group->post('/leagues/{id}/play-next', [SimulationController::class, 'playNextWeek']);
            $group->post('/leagues/{id}/play-all', [SimulationController::class, 'playAllWeeks']);
            $group->put('/leagues/{leagueId}/games/{gameId}', [SimulationController::class, 'updateMatchResult']);
        });
    }

    public function testInitializeLeague(): void
    {
        // Prepare test data
        $requestData = [
            'teams' => [
                ['name' => 'Team 1', 'strength' => 'high'],
                ['name' => 'Team 2', 'strength' => 'medium'],
                ['name' => 'Team 3', 'strength' => 'medium'],
                ['name' => 'Team 4', 'strength' => 'low'],
            ]
        ];

        // Mock team repository to capture created teams
        $this->teamRepository->expects($this->exactly(4))
            ->method('save')
            ->willReturnCallback(function (Team $team) {
                // Set ID for newly created team
                $reflectionTeam = new \ReflectionClass(Team::class);
                $idProperty = $reflectionTeam->getProperty('id');
                $idProperty->setAccessible(true);
                $idProperty->setValue($team, rand(1, 1000));
            });

        // Mock league repository to capture created league
        $leagueId = 1;
        $createdLeague = null;

        $this->leagueTableRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (LeagueTable $league) use (&$createdLeague, $leagueId) {
                // Set ID for newly created league
                $reflectionLeague = new \ReflectionClass(LeagueTable::class);
                $idProperty = $reflectionLeague->getProperty('id');
                $idProperty->setAccessible(true);
                $idProperty->setValue($league, $leagueId);

                $createdLeague = $league;
            });

        // Mock findById to return the created league
        $this->leagueTableRepository->expects($this->once())
            ->method('findById')
            ->with($leagueId)
            ->willReturnCallback(function () use (&$createdLeague) {
                return $createdLeague;
            });

        // Create request
        $request = new ServerRequestFactory()
            ->createServerRequest('POST', '/api/leagues')
            ->withHeader('Content-Type', 'application/json')
            ->withParsedBody($requestData);

        // Process request
        $response = $this->app->handle($request);

        // Assertions
        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('league', $responseData);
        $this->assertArrayHasKey('teams', $responseData['league']);
        $this->assertCount(4, $responseData['league']['teams']);
    }

    public function testGetCurrentLeague(): void
    {
        // Create test league
        $team1 = new Team('Team A', TeamStrength::fromValue(80));
        $team2 = new Team('Team B', TeamStrength::fromValue(70));
        $team3 = new Team('Team C', TeamStrength::fromValue(75));
        $team4 = new Team('Team D', TeamStrength::fromValue(65));

        // Set IDs for teams
        $reflectionTeam = new \ReflectionClass(Team::class);
        $idProperty = $reflectionTeam->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($team1, 1);
        $idProperty->setValue($team2, 2);
        $idProperty->setValue($team3, 3);
        $idProperty->setValue($team4, 4);

        $teams = [$team1, $team2, $team3, $team4];
        $league = new LeagueTable($teams);

        // Set ID for league
        $reflectionLeague = new \ReflectionClass(LeagueTable::class);
        $leagueIdProperty = $reflectionLeague->getProperty('id');
        $leagueIdProperty->setAccessible(true);
        $leagueIdProperty->setValue($league, 1);

        // Set IDs and leagueTable for all Game objects in fixtures
        $reflectionGame = new \ReflectionClass(Game::class);
        $gameIdProperty = $reflectionGame->getProperty('id');
        $gameIdProperty->setAccessible(true);

        $fixturesProperty = $reflectionLeague->getProperty('fixtures');
        $fixturesProperty->setAccessible(true);
        $fixtures = $fixturesProperty->getValue($league);

        foreach ($fixtures as $index => $game) {
            $gameIdProperty->setValue($game, $index + 1);
            $game->setLeagueTable($league);
        }

        // Mock repository to return our test league
        $this->leagueTableRepository->expects($this->once())
            ->method('findCurrent')
            ->willReturn($league);

        // Create request
        $request = new ServerRequestFactory()
            ->createServerRequest('GET', '/api/leagues/current');

        // Process request
        $response = $this->app->handle($request);

        // Assertions
        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertArrayHasKey('league', $responseData);
        $this->assertArrayHasKey('id', $responseData['league']);
        $this->assertArrayHasKey('teams', $responseData['league']);
        $this->assertCount(4, $responseData['league']['teams']);
    }

    public function testPlayNextWeek(): void
    {
        // Create test league with teams
        $team1 = new Team('Team A', TeamStrength::fromValue(80));
        $team2 = new Team('Team B', TeamStrength::fromValue(70));
        $team3 = new Team('Team C', TeamStrength::fromValue(75));
        $team4 = new Team('Team D', TeamStrength::fromValue(65));

        // Set IDs for teams
        $reflectionTeam = new \ReflectionClass(Team::class);
        $idProperty = $reflectionTeam->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($team1, 1);
        $idProperty->setValue($team2, 2);
        $idProperty->setValue($team3, 3);
        $idProperty->setValue($team4, 4);

        $teams = [$team1, $team2, $team3, $team4];
        $league = new LeagueTable($teams);

        // Set ID for league
        $reflectionLeague = new \ReflectionClass(LeagueTable::class);
        $leagueIdProperty = $reflectionLeague->getProperty('id');
        $leagueIdProperty->setAccessible(true);
        $leagueIdProperty->setValue($league, 1);

        // Set IDs and leagueTable for all Game objects in fixtures
        $reflectionGame = new \ReflectionClass(Game::class);
        $gameIdProperty = $reflectionGame->getProperty('id');
        $gameIdProperty->setAccessible(true);

        $fixturesProperty = $reflectionLeague->getProperty('fixtures');
        $fixturesProperty->setAccessible(true);
        $fixtures = $fixturesProperty->getValue($league);

        foreach ($fixtures as $index => $game) {
            $gameIdProperty->setValue($game, $index + 1);
            $game->setLeagueTable($league);
        }

        // Mock findById to return our test league
        $this->leagueTableRepository->expects($this->exactly(2))
            ->method('findById')
            ->with(1)
            ->willReturn($league);

        // Mock save method for league
        $this->leagueTableRepository->expects($this->once())
            ->method('save')
            ->with($league);

        // Mock save method for teams
        $this->teamRepository->expects($this->exactly(4))
            ->method('save')
            ->with($this->isInstanceOf(Team::class));

        // Create request
        $request = new ServerRequestFactory()
            ->createServerRequest('POST', '/api/leagues/1/play-next');

        // Process request
        $response = $this->app->handle($request);

        // Assertions
        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('results', $responseData);
        $this->assertArrayHasKey('league', $responseData);

        // Should have 2 games in the results (for 4 teams)
        $this->assertCount(2, $responseData['results']);
    }

    public function testPlayAllWeeks(): void
    {
        // Create test league with teams
        $team1 = new Team('Team A', TeamStrength::fromValue(80));
        $team2 = new Team('Team B', TeamStrength::fromValue(70));
        $team3 = new Team('Team C', TeamStrength::fromValue(75));
        $team4 = new Team('Team D', TeamStrength::fromValue(65));

        // Set IDs for teams
        $reflectionTeam = new \ReflectionClass(Team::class);
        $idProperty = $reflectionTeam->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($team1, 1);
        $idProperty->setValue($team2, 2);
        $idProperty->setValue($team3, 3);
        $idProperty->setValue($team4, 4);

        $teams = [$team1, $team2, $team3, $team4];
        $league = new LeagueTable($teams);

        // Set ID for league
        $reflectionLeague = new \ReflectionClass(LeagueTable::class);
        $leagueIdProperty = $reflectionLeague->getProperty('id');
        $leagueIdProperty->setAccessible(true);
        $leagueIdProperty->setValue($league, 1);

        // Set IDs and leagueTable for all Game objects in fixtures
        $reflectionGame = new \ReflectionClass(Game::class);
        $gameIdProperty = $reflectionGame->getProperty('id');
        $gameIdProperty->setAccessible(true);

        $fixturesProperty = $reflectionLeague->getProperty('fixtures');
        $fixturesProperty->setAccessible(true);
        $fixtures = $fixturesProperty->getValue($league);

        foreach ($fixtures as $index => $game) {
            $gameIdProperty->setValue($game, $index + 1);
            $game->setLeagueTable($league);
        }

        // Mock findById to return our test league
        $this->leagueTableRepository->expects($this->exactly(2))
            ->method('findById')
            ->with(1)
            ->willReturn($league);

        // Mock save method for league
        $this->leagueTableRepository->expects($this->once())
            ->method('save')
            ->with($league);

        // Mock save method for teams
        $this->teamRepository->expects($this->exactly(4))
            ->method('save')
            ->with($this->isInstanceOf(Team::class));

        // Create request
        $request = new ServerRequestFactory()
            ->createServerRequest('POST', '/api/leagues/1/play-all');

        // Process request
        $response = $this->app->handle($request);

        // Assertions
        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('results', $responseData);
        $this->assertArrayHasKey('league', $responseData);

        // 4 teams means 6 weeks of play (3*2)
        $this->assertCount(6, $responseData['results']);

        // Verify the league is now marked as completed
        $this->assertTrue($responseData['league']['isCompleted']);
    }

    public function testUpdateMatchResult(): void
    {
        // Create test league with teams
        $team1 = new Team('Team A', TeamStrength::fromValue(80));
        $team2 = new Team('Team B', TeamStrength::fromValue(70));
        $team3 = new Team('Team C', TeamStrength::fromValue(75));
        $team4 = new Team('Team D', TeamStrength::fromValue(65));

        // Set IDs for teams
        $reflectionTeam = new \ReflectionClass(Team::class);
        $idProperty = $reflectionTeam->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($team1, 1);
        $idProperty->setValue($team2, 2);
        $idProperty->setValue($team3, 3);
        $idProperty->setValue($team4, 4);

        $teams = [$team1, $team2, $team3, $team4];
        $league = new LeagueTable($teams);

        // Set ID for league
        $reflectionLeague = new \ReflectionClass(LeagueTable::class);
        $leagueIdProperty = $reflectionLeague->getProperty('id');
        $leagueIdProperty->setAccessible(true);
        $leagueIdProperty->setValue($league, 1);

        // Create a game for the update
        $game = new Game($team1, $team2, 1);

        // Set ID for game
        $reflectionGame = new \ReflectionClass(Game::class);
        $gameIdProperty = $reflectionGame->getProperty('id');
        $gameIdProperty->setAccessible(true);
        $gameIdProperty->setValue($game, 1);

        // Set the league for the game
        $game->setLeagueTable($league);

        // Replace fixtures with the test game
        $fixturesProperty = $reflectionLeague->getProperty('fixtures');
        $fixturesProperty->setAccessible(true);
        $fixtures = $fixturesProperty->getValue($league);
        if ($fixtures instanceof \Doctrine\Common\Collections\Collection) {
            $fixtures->clear();
            $fixtures->add($game);
        } else {
            $fixturesProperty->setValue($league, [$game]);
        }

        // Mock findById to return our test league
        $this->leagueTableRepository->expects($this->exactly(2))
            ->method('findById')
            ->with(1)
            ->willReturn($league);

        // Mock save method for league
        $this->leagueTableRepository->expects($this->once())
            ->method('save')
            ->with($league);

        // Mock save method for teams
        $this->teamRepository->expects($this->exactly(2))
            ->method('save')
            ->with($this->isInstanceOf(Team::class));

        // Prepare request data
        $requestData = [
            'homeGoals' => 3,
            'awayGoals' => 1
        ];

        // Create request
        $request = new ServerRequestFactory()
            ->createServerRequest('PUT', '/api/leagues/1/games/1')
            ->withHeader('Content-Type', 'application/json')
            ->withParsedBody($requestData);

        // Process request
        $response = $this->app->handle($request);

        // Assertions
        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('league', $responseData);
        $this->assertEquals('Match result updated successfully', $responseData['message']);
    }

    public function testInitializeLeagueWithInvalidData(): void
    {
        // Prepare invalid test data - not enough teams
        $requestData = [
            'teams' => [
                ['name' => 'Team 1', 'strength' => 'high'],
                ['name' => 'Team 2', 'strength' => 'medium'],
                // Missing 2 teams
            ]
        ];

        // Create request
        $request = new ServerRequestFactory()
            ->createServerRequest('POST', '/api/leagues')
            ->withHeader('Content-Type', 'application/json')
            ->withParsedBody($requestData);

        // Process request
        $response = $this->app->handle($request);

        // Assertions
        $this->assertEquals(400, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('teams', $responseData['errors']);
    }

    public function testPlayNextWeekWithInvalidLeagueId(): void
    {
        // Mock findById to return null for non-existent league
        $this->leagueTableRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        // Create request
        $request = new ServerRequestFactory()
            ->createServerRequest('POST', '/api/leagues/999/play-next');

        // Process request
        $response = $this->app->handle($request);

        // Assertions
        $this->assertEquals(400, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('League with ID 999 not found', $responseData['error']);
    }
}