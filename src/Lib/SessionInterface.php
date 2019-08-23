<?php

declare(strict_types=1);

namespace Stu\Lib;

use UserData;

interface SessionInterface
{
    public function createSession(bool $session_check = true): void;

    public function checkLoginCookie(): void;

    public function getUser(): ?UserData;

    public function logout(): void;

    public function storeSessionData($key, $value): void;

    public function deleteSessionData($key, $value): void;

    public function hasSessionValue($key, $value): bool;

    public function login(string $userName, string $password): void;
}