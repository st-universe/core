<?php

namespace Stu\Module\Api\Middleware\Request;

use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use Stu\Module\Api\Middleware\Action;

interface JsonSchemaRequestInterface
{

    public function setRequest(ServerRequestInterface $request): void;

    public function getData(Action $action): stdClass;
}