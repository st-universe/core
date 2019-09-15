<?php

namespace Stu\Module\Api\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use User;

interface SessionInterface
{
    public function getUser(): User;

    public function resumeSession(ServerRequestInterface $request): void;
}