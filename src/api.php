<?php

declare(strict_types=1);

use Fig\Http\Message\StatusCodeInterface;
use Noodlehaus\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Stu\Module\Api\V1\Common\Login\Login;
use Stu\Module\Api\V1\Common\News\GetNews;

require_once __DIR__ . '/inc/config.inc.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->add(new Tuupola\Middleware\JwtAuthentication([
    'secret' => $container->get(ConfigInterface::class)->get('api.jwt_secret'),
    'secure' => true,
    'relaxed' => ['localhost'],
    'ignore' => [
        '/api/v1/common/login',
    ],
    'error' => function (ResponseInterface $response, array $arguments): void {
        $data['statusCode'] = StatusCodeInterface::STATUS_UNAUTHORIZED;
        $data['error'] = $arguments['message'];
        $response->withHeader('Content-Type', 'application/json')
            ->getBody()
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    },
]));

$app->group('/api/v1', function (RouteCollectorProxy $group): void {
    $group->get('/common/news', GetNews::class);
    $group->post('/common/login', Login::class);
});

$app->run();