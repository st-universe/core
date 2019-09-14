<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Stu\Module\Api\V1\Common\Login\Login;
use Stu\Module\Api\V1\Common\News\GetNews;

require_once __DIR__.'/inc/config.inc.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->group('/api/v1', function (RouteCollectorProxy $group): void {
    $group->get('/common/news', GetNews::class);
    $group->post('/common/login', Login::class);
});

$app->run();