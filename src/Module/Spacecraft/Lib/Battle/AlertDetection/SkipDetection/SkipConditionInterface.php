<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection\SkipDetection;

use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

interface SkipConditionInterface
{
    public function isSkipped(
        User $incomingShipUser,
        Spacecraft $alertedSpacecraft,
        User $alertedUser,
        int $time
    ): bool;
}
