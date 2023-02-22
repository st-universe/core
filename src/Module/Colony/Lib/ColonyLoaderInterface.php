<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;

interface ColonyLoaderInterface
{
    public function byIdAndUser(
        int $colonyId,
        int $userId,
        bool $checkForEntityLock = true
    ): ColonyInterface;
}
