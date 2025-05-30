<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\UserInterface;

interface SessionInterface
{
    public function createSession(bool $session_check = true): void;

    public function checkLoginCookie(): void;

    public function getUser(): ?UserInterface;

    public function logout(?UserInterface $user = null): void;

    public function storeSessionData(string $key, mixed $value, bool $isSingleValue = false): void;

    public function deleteSessionData(string $key, mixed $value = null): void;

    public function hasSessionValue(string $key, mixed $value): bool;

    public function getSessionValue(string $key): mixed;

    public function login(string $login, string $password): bool;
}
