<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Orm\Entity\UserInterface;

interface SessionInterface
{
    public function createSession(bool $session_check = true): void;

    public function checkLoginCookie(): void;

    public function getUser(): ?UserInterface;

    public function logout(?UserInterface $user = null): void;

    public function storeSessionData($key, $value, bool $isSingleValue = false): void;

    public function deleteSessionData($key, $value = null): void;

    public function hasSessionValue($key, $value): bool;

    public function getSessionValue($key);

    public function login(string $login, string $password): bool;
}
