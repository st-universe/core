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

    /**
     * @param string $key
     */
    public function storeSessionData($key, mixed $value, bool $isSingleValue = false): void;

    /**
     * @param string $key
     */
    public function deleteSessionData($key, mixed $value = null): void;

    /**
     * @param string $key
     */
    public function hasSessionValue($key, mixed $value): bool;

    /**
     * @param string $key
     */
    public function getSessionValue($key);

    public function login(string $login, string $password): bool;
}
