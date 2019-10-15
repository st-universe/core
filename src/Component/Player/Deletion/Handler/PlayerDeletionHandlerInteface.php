<?php

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;

interface PlayerDeletionHandlerInteface
{
    public function delete(UserInterface $user): void;
}
