<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\StatusCodeInterface;
use Noodlehaus\ConfigInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Routing\RouteCollectorProxy;
use Stu\Module\Api\Middleware\Emitter\ResponseEmitter;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\Module\Api\Middleware\Response\ReponseFactory;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Api\V1\Colony\ColonyList\GetColonyList;
use Stu\Module\Api\V1\Colony\GetById\GetColonyById;
use Stu\Module\Api\V1\Common\Faction\GetFactions;
use Stu\Module\Api\V1\Common\Login\Login;
use Stu\Module\Api\V1\Common\News\GetNews;

require_once __DIR__ . '/inc/config.inc.php';

$app = AppFactory::create(
    new ReponseFactory(),
    $container
);

$app->add(new Tuupola\Middleware\JwtAuthentication([
    'secret' => $container->get(ConfigInterface::class)->get('api.jwt_secret'),
    'secure' => true,
    'relaxed' => ['localhost'],
    'ignore' => [
        '/api/v1/common',
    ],
    'error' => function (ResponseInterface $response, array $arguments): void {
        $data['statusCode'] = StatusCodeInterface::STATUS_UNAUTHORIZED;
        $data['error'] = $arguments['message'];
        $response->withHeader('Content-Type', 'application/json')
            ->getBody()
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    },
    'before' => function (ServerRequestInterface $request, array $arguments) use ($container): void {
        $container->get(SessionInterface::class)->resumeSession($request);
    }
]));

$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use($container): ResponseInterface {
    $container->get(JsonSchemaRequestInterface::class)->setRequest($request);
    return $handler->handle($request);
});

$app->group('/api/v1/common', function (RouteCollectorProxy $group): void {
    $group->get('/news', GetNews::class);
    $group->post('/login', Login::class);
    $group->get('/faction', GetFactions::class);
});

$app->group('/api/v1/colony', function (RouteCollectorProxy $group): void {
    $group->get('/', GetColonyList::class);
    $group->get('/{colonyId}', GetColonyById::class);
});

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

$response = $app->handle($request);

$entityManager->commit();

$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);

