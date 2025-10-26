<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Orm\Entity\User;

final class ColonyDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private ColonyResetterInterface $colonyResetter)
    {
    }

    #[\Override]
    public function delete(User $user): void
    {
        foreach ($user->getColonies()->toArray() as $colony) {
            $this->colonyResetter->reset($colony, false);
        }
    }
}
