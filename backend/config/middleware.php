<?php

declare(strict_types=1);

use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;

return function (App $app) {
    // Parse JSON, form data and XML
    $app->addBodyParsingMiddleware();

    // Content Length
    $app->add(new ContentLengthMiddleware());

    // CORS middleware
    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);

        $allowedOrigins = $_ENV['CORS_ALLOW_ORIGINS'] ?? '*';
        $allowedOrigins = explode(',', $allowedOrigins);

        $origin = $request->getHeaderLine('Origin');
        if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
            return $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    });
};