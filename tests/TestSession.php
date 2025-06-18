<?php

declare(strict_types=1);

namespace Stu;

use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class TestSession implements SessionInterface
{
    public const DEFAULT_USER_ID = 101;

    private ?UserInterface $user = null;

    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function setUser(int $userId): void
    {
        if (
            $this->user === null
            || $this->user->getId() !== $userId
        ) {
            $this->user = $this->userRepository->find($userId);
        }
    }

    public function createSession(bool $session_check = true): void {}

    public function checkLoginCookie(): void {}

    public function getUser(): ?UserInterface
    {
        if ($this->user === null) {
            $this->user = $this->userRepository->find(self::DEFAULT_USER_ID);
        }
        return $this->user;
    }

    public function logout(?UserInterface $user = null): void {}

    public function storeSessionData($key, mixed $value, bool $isSingleValue = false): void {}

    public function deleteSessionData($key, mixed $value = null): void {}

    public function hasSessionValue($key, mixed $value): bool
    {
        return false;
    }

    public function getSessionValue($key) {}

    public function login(string $login, string $password): bool
    {
        return true;
    }
}
