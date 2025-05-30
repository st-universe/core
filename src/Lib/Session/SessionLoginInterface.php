<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

interface SessionLoginInterface
{
    public function checkLoginCookie(): void;

    public function login(string $login, string $password): bool;
}
