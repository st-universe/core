<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Stu\Module\Api\V1\News\GetNews;

require_once __DIR__.'/inc/config.inc.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

$afterMiddleware = function (Request $request, RequestHandler $handler) {
    $response = $handler->handle($request);
    $response->getBody()->write(json_encode($response->getBody()));
    return $response;
};

$app->add($afterMiddleware);

$app->group('/api/v1', function (RouteCollectorProxy $group): void {
    $group->get('/public/news', function (Request $request, Response $response, array $args): Response {
        $service = $this->get(GetNews::class);

        $response->getBody()->write(json_encode($service->handle()));
        return $response->withHeader('Content-Type', 'application/json');
    });
});

$app->run();