<?php

namespace Stu\Component\Ship\System\Utility;

use Stu\Orm\Entity\ShipInterface;

interface TractorMassPayloadUtilInterface
{
    public function tryToTow(ShipInterface $ship, ShipInterface $tractoredShip): ?string;

    public function tractorSystemSurvivedTowing(ShipInterface $ship, ShipInterface $tractoredShip, &$informations): bool;
}
