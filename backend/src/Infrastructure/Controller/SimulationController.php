<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\DTO\TeamDTO;
use App\Application\Service\LeagueService;
use App\Application\Service\SimulationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;

class SimulationController
{
    private LeagueService $leagueService;
    private SimulationService $simulationService;

    public function __construct(
        LeagueService $leagueService,
        SimulationService $simulationService
    ) {
        $this->leagueService = $leagueService;
        $this->simulationService = $simulationService;
    }

    public function initializeLeague(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validate request
        $validator = new Validator($data);
        $validator->rule('required', 'teams')->rule('array', 'teams');
        if (count($data['teams']) !== 4) {
            return $this->jsonResponse($response, [
                'errors' => ['teams' => ['Exactly 4 teams are required']]
            ], 400);
        }

        if (!$validator->validate()) {
            return $this->jsonResponse($response, ['errors' => $validator->errors()], 400);
        }

        $teamDTOs = array_map(function ($teamData) {
            return new TeamDTO(
                0,
                $teamData['name'],
                $teamData['strength'] ?? 'medium',
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0
            );
        }, $data['teams']);

        $leagueId = $this->leagueService->createNewLeague($teamDTOs);
        $league = $this->leagueService->getLeagueById($leagueId);

        return $this->jsonResponse($response, [
            'message' => 'League initialized successfully',
            'league' => $league->toArray()
        ]);
    }

    public function getCurrentLeague(Request $request, Response $response): Response
    {
        $league = $this->leagueService->getCurrentLeague();

        if (!$league) {
            return $this->jsonResponse($response, ['error' => 'No active league found'], 404);
        }

        return $this->jsonResponse($response, ['league' => $league->toArray()]);
    }

    public function playNextWeek(Request $request, Response $response, array $args): Response
    {
        $leagueId = (int)$args['id'];

        try {
            $results = $this->simulationService->playNextWeek($leagueId);
            $league = $this->leagueService->getLeagueById($leagueId);

            return $this->jsonResponse($response, [
                'message' => 'Week ' . $league->currentWeek . ' played successfully',
                'results' => array_map(fn($result) => $result->toArray(), $results),
                'league' => $league->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function playAllWeeks(Request $request, Response $response, array $args): Response
    {
        $leagueId = (int)$args['id'];

        try {
            $weeklyResults = $this->simulationService->playAllRemainingWeeks($leagueId);
            $league = $this->leagueService->getLeagueById($leagueId);

            $formattedResults = [];
            foreach ($weeklyResults as $week => $results) {
                $formattedResults[$week] = array_map(fn($result) => $result->toArray(), $results);
            }

            return $this->jsonResponse($response, [
                'message' => 'All remaining weeks played successfully',
                'results' => $formattedResults,
                'league' => $league->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function updateMatchResult(Request $request, Response $response, array $args): Response
    {
        $leagueId = (int)$args['leagueId'];
        $gameId = (int)$args['gameId'];
        $data = $request->getParsedBody();

        // Validate request
        $validator = new Validator($data);
        $validator->rule('required', ['homeGoals', 'awayGoals']);
        $validator->rule('integer', ['homeGoals', 'awayGoals']);
        $validator->rule('min', ['homeGoals', 'awayGoals'], 0);

        if (!$validator->validate()) {
            return $this->jsonResponse($response, ['errors' => $validator->errors()], 400);
        }

        try {
            $this->simulationService->updateGameResult(
                $leagueId,
                $gameId,
                (int)$data['homeGoals'],
                (int)$data['awayGoals']
            );

            $league = $this->leagueService->getLeagueById($leagueId);

            return $this->jsonResponse($response, [
                'message' => 'Match result updated successfully',
                'league' => $league->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}