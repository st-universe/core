<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpUnauthorizedException;
use User;

final class Session implements SessionInterface {

    private $user;

    public function getUser(): User {
        if ($this->user === null) {
            throw new Exception('user not validated');
        }

        return $this->user;
    }

    public function resumeSession(ServerRequestInterface $request): void
    {
        $userData = $request->getAttribute('token')['stu'] ?? null;

        if ($userData === null) {
            throw new HttpUnauthorizedException($request);
        }

        $this->user = new User((int) $userData->uid);
    }
}