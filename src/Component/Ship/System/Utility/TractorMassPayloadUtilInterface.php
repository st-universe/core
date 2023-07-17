<?php

namespace Stu\Component\Ship\System\Utility;

use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface TractorMassPayloadUtilInterface
{
    public function tryToTow(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip
    ): ?string;

    public function tractorSystemSurvivedTowing(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        InformationWrapper $informations
    ): bool;
}
