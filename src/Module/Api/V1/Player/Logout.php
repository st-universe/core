<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Lib\SessionInterface as InternalSessionInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;

final class Logout extends Action
{
    private SessionInterface $session;

    private InternalSessionInterface $internalSession;

    public function __construct(
        SessionInterface $session,
        InternalSessionInterface $internalSession
    ) {
        $this->session = $session;
        $this->internalSession = $internalSession;
    }

    public function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $this->internalSession->logout($this->session->getUser());

        return $response->withData(true);
    }
}
