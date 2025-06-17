<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection\SkipDetection;

use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface SkipConditionInterface
{
    public function isSkipped(
        UserInterface $incomingShipUser,
        SpacecraftInterface $alertedSpacecraft,
        UserInterface $alertedUser,
        int $time
    ): bool;
}
