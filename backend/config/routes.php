<?php

declare(strict_types=1);

use App\Infrastructure\Controller\SimulationController;
use Slim\App;

return static function (App $app) {

    $app->group('/api', function (\Slim\Routing\RouteCollectorProxy $group) {
        // League Management
        $group->post('/leagues', [SimulationController::class, 'initializeLeague']);
        $group->get('/leagues/current', [SimulationController::class, 'getCurrentLeague']);

        // Simulation Control
        $group->post('/leagues/{id}/play-next', [SimulationController::class, 'playNextWeek']);
        $group->post('/leagues/{id}/play-all', [SimulationController::class, 'playAllWeeks']);
        $group->put('/leagues/{leagueId}/games/{gameId}', [SimulationController::class, 'updateMatchResult']);
    });

};