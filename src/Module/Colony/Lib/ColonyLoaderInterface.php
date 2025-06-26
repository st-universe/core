<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\Colony;

interface ColonyLoaderInterface
{
    public function loadWithOwnerValidation(
        int $colonyId,
        int $userId,
        bool $checkForEntityLock = true
    ): Colony;

    public function load(int $colonyId, bool $checkForEntityLock = true): Colony;
}
