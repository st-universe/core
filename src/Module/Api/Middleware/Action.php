<?php
declare(strict_types=1);

namespace Stu\Module\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;

abstract class Action
{
    /** @var null|string */
    public const JSON_SCHEMA_FILE = null;

    public function __invoke(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): ResponseInterface {

        return $this->action($request, $response, $args);
    }

    abstract protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface;
}
