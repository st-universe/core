<?php

declare(strict_types=1);

namespace Stu\Module\Api;

use Stu\Module\Api\Middleware\Request\JsonSchemaRequest;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\Module\Api\Middleware\Session;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Api\V1\Common\News\GetNews;
use Stu\Module\Index\Action\Login\Login;
use function DI\autowire;

return [
    SessionInterface::class => autowire(Session::class),
    JsonSchemaRequestInterface::class => autowire(JsonSchemaRequest::class),
    GetNews::class => autowire(GetNews::class),
    Login::class => autowire(Login::class),
];