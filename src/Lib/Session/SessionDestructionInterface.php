<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\User;

interface SessionDestructionInterface
{
    public function destroySession(SessionInterface $session, ?User $user = null): void;
}
