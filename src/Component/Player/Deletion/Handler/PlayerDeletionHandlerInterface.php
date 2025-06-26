<?php

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\User;

interface PlayerDeletionHandlerInterface
{
    public function delete(User $user): void;
}
