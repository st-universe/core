<?php

namespace Stu\Module\Api\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Orm\Entity\UserInterface;

interface SessionInterface
{
    public function getUser(): UserInterface;

    public function resumeSession(ServerRequestInterface $request): void;
}