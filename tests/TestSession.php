<?php

declare(strict_types=1);

namespace Stu;

use Stu\Lib\Session\SessionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class TestSession implements SessionInterface
{
    public const DEFAULT_USER_ID = 101;

    private ?UserInterface $user = null;

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

    public function setUser(?UserInterface $user): SessionInterface
    {
        return $this;
    }

    public function createSession(bool $session_check = true): void {}

    public function getUser(): ?UserInterface
    {
        if ($this->user === null) {
            $this->user = $this->userRepository->find(self::DEFAULT_USER_ID);
        }
        return $this->user;
    }

    public function logout(?UserInterface $user = null): void {}
}
