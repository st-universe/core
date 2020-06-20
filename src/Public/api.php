<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\StatusCodeInterface;
use Noodlehaus\ConfigInterface;
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
use Stu\Module\Api\V1\Player\Logout;
use Stu\Module\Api\V1\Common\News\GetNews;
use Stu\Module\Api\V1\Common\Register\Register;
use Stu\Module\Api\V1\Player\GetInfo;
use Stu\Module\Api\V1\Player\GetNewPrivateMessages;
use Stu\Module\Api\V1\Player\Research\CancelResearch;
use Stu\Module\Api\V1\Player\Research\CurrentResearch;
use Stu\Module\Api\V1\Player\Research\GetResearch;
use Stu\Module\Api\V1\Player\Research\ResearchList;
use Stu\Module\Api\V1\Player\Research\StartResearch;

require_once __DIR__ . '/../Config/Bootstrap.php';

$app = AppFactory::create(
    new ReponseFactory(),
    $container
);

$apiErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $payload = [
        'error' => [
            'errorcode' => null,
            'error' => $exception->getMessage(),
        ]
    ];

    $response = $app->getResponseFactory()
        ->createResponse()
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($exception->getCode());

    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );

    return $response;
};

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($apiErrorHandler);

$app->add(new Tuupola\Middleware\JwtAuthentication([
    'secret' => $container->get(ConfigInterface::class)->get('api.jwt_secret'),
    'secure' => true,
    'relaxed' => ['localhost'],
    'ignore' => [
        '/api/v1/common',
    ],
    'error' => function (ResponseInterface $response, array $arguments): void {
        $error = [
            'error' => [
                'errorCode' => null,
                'error' => 'Unauthorized'
            ]
        ];

        $response->withHeader('Content-Type', 'application/json')
            ->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED)
            ->getBody()
            ->write(json_encode($error, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    },
    'before' => function (ServerRequestInterface $request, array $arguments) use ($container): void {
        $container->get(SessionInterface::class)->resumeSession($request);
    }
]));

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use($container): ResponseInterface {
    $container->get(JsonSchemaRequestInterface::class)->setRequest($request);
    return $handler->handle($request);
});

$app->group('/api/v1/common', function (RouteCollectorProxy $group): void {
    $group->get('/news', GetNews::class);
    $group->post('/login', Login::class);
    $group->get('/faction', GetFactions::class);

    $group->post('/player/new', Register::class);
});

$app->group('/api/v1/colony', function (RouteCollectorProxy $group): void {
    $group->get('', GetColonyList::class);
    $group->get('/{colonyId}', GetColonyById::class);
});

$app->group('/api/v1/player', function (RouteCollectorProxy $group): void {
    $group->get('', GetInfo::class);
    $group->get('/newpms', GetNewPrivateMessages::class);
    $group->post('/logout', Logout::class);

    $group->get('/research/current', CurrentResearch::class);
    $group->get('/research', ResearchList::class);
    $group->get('/research/{researchId}', GetResearch::class);
    $group->post('/research/cancel', CancelResearch::class);
    $group->post('/research/start', StartResearch::class);
});

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

$response = $app->handle($request);

$entityManager->commit();

$responseEmitter = new ResponseEmitter(
    $container->get(ConfigInterface::class)
);
$responseEmitter->emit($response);

