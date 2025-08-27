<?php

declare(strict_types=1);

namespace Stu;

use Stu\Lib\Session\SessionInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

class TestSession implements SessionInterface
{
    public const DEFAULT_USER_ID = 101;

    private ?User $user = null;

    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function setUserById(int $userId): void
    {
        if (
            $this->user === null
            || $this->user->getId() !== $userId
        ) {
            $this->user = $this->userRepository->find($userId);
        }
    }

    public function setUser(?User $user): SessionInterface
    {
        return $this;
    }

    public function createSession(bool $session_check = true): void
    {
        // nothing to do here
    }

    public function getUser(): ?User
    {
        if ($this->user === null) {
            $this->user = $this->userRepository->find(self::DEFAULT_USER_ID);
        }
        return $this->user;
    }

    public function logout(?User $user = null): void
    {
        // nothing to do here
    }
}
