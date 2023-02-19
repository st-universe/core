<?php

namespace Stu\Component\Ship\System\Utility;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface TractorMassPayloadUtilInterface
{
    public function tryToTow(ShipWrapperInterface $wrapper, ShipInterface $tractoredShip): ?string;

    /**
     * @param list<string> $informations
     */
    public function tractorSystemSurvivedTowing(ShipWrapperInterface $wrapper, ShipInterface $tractoredShip, array &$informations): bool;
}
