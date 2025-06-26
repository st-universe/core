<?php

namespace Stu\Lib\Session;

use Stu\Orm\Entity\User;

interface SessionDestructionInterface
{
    public function destroySession(SessionInterface $session, ?User $user = null): void;
}
