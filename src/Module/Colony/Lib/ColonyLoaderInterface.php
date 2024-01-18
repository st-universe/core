<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;

interface ColonyLoaderInterface
{
    public function loadWithOwnerValidation(
        int $colonyId,
        int $userId,
        bool $checkForEntityLock = true
    ): ColonyInterface;

    public function load(int $colonyId, bool $checkForEntityLock = true): ColonyInterface;
}
