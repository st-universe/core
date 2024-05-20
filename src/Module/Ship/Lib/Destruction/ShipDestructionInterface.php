<?php

namespace Stu\Module\Ship\Lib\Destruction;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipDestructionInterface
{
    /**
     * Destroys a ship and replaces it by a nice debrisfield,
     * also starts escape pods if present
     */
    public function destroy(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void;
}
