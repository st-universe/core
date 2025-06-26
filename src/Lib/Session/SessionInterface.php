<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\User;

interface SessionInterface
{
    public function createSession(bool $session_check = true): void;

    public function getUser(): ?User;

    public function setUser(?User $user): SessionInterface;

    public function logout(?User $user = null): void;
}
