<?php

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;

interface PlayerDeletionHandlerInterface
{
    public function delete(UserInterface $user): void;
}
