<?php

namespace Stu\Lib\Session;

use Stu\Orm\Entity\UserInterface;

interface SessionDestructionInterface
{
    public function destroySession(SessionInterface $session, ?UserInterface $user = null): void;
}
