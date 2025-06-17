<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\UserInterface;

interface SessionInterface
{
    public function createSession(bool $session_check = true): void;

    public function getUser(): ?UserInterface;

    public function setUser(?UserInterface $user): SessionInterface;

    public function logout(?UserInterface $user = null): void;
}
