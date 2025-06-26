<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\User;

interface AlertedShipsDetectionInterface
{
    /** @return Collection<int, SpacecraftWrapperInterface> */
    public function getAlertedShipsOnLocation(
        Location $location,
        User $user
    ): Collection;
}
