<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface AlertedShipsDetectionInterface
{
    /** @return Collection<int, ShipWrapperInterface> */
    public function getAlertedShipsOnLocation(ShipInterface $incomingShip): Collection;
}
