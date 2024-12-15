<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\UserInterface;

interface AlertedShipsDetectionInterface
{
    /** @return Collection<int, SpacecraftWrapperInterface> */
    public function getAlertedShipsOnLocation(
        LocationInterface $location,
        UserInterface $user
    ): Collection;
}
