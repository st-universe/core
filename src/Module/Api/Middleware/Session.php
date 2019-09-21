<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpUnauthorizedException;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Session implements SessionInterface
{

    private $user;

    private $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function getUser(): UserInterface
    {
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

        $this->user = $this->userRepository->find((int)$userData->uid);
    }
}