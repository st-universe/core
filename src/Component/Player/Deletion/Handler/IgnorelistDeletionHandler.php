<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;

final class IgnorelistDeletionHandler implements PlayerDeletionHandlerInteface
{

    public function delete(UserInterface $user): void
    {
        // @todo Implement delete() method.
    }
}
