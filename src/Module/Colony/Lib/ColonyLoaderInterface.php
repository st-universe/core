<?php

namespace Stu\Module\Colony\Lib;

use Colony;

interface ColonyLoaderInterface
{
    public function byId(int $colonyId): Colony;

    public function byIdAndUser(int $colonyId, int $userId): Colony;
}