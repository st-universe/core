<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Party\AlertedBattlePartyInterface;
use Stu\Module\Ship\Lib\Battle\Party\IncomingBattleParty;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface AlertReactionFacadeInterface
{
    public function doItAll(
        ShipWrapperInterface $incomingShipWrapper,
        InformationInterface $informations,
        ?ShipInterface $tractoringShip = null
    ): void;

    public function performAttackCycle(
        AlertedBattlePartyInterface $alertedParty,
        IncomingBattleParty $incomingParty,
        InformationWrapper $informations
    ): void;
}
