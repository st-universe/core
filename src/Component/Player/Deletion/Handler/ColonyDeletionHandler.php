<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Orm\Entity\UserInterface;

final class ColonyDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ColonyResetterInterface $colonyResetter;

    public function __construct(
        ColonyResetterInterface $colonyResetter
    ) {
        $this->colonyResetter = $colonyResetter;
    }

    public function delete(UserInterface $user): void
    {
        foreach ($user->getColonies()->toArray() as $colony) {
            $this->colonyResetter->reset($colony, false);
        }
    }
}
