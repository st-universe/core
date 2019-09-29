<?php
declare(strict_types=1);

namespace Stu\Module\Api\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
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

        try {
            return $this->action($request, $response, $args);
        } catch (Exception $e) {
            throw new HttpNotFoundException($request, $e->getMessage());
        }
    }

    abstract protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface;
}
