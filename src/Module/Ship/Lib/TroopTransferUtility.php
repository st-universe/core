<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\TroopQuartersShipSystem;
use Stu\Orm\Entity\ShipInterface;

final class TroopTransferUtility implements TroopTransferUtilityInterface
{
    public function getFreeQuarters(ShipInterface $ship): int
    {
        $free = $ship->getMaxCrewCount() - $ship->getCrewCount();

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            $free += TroopQuartersShipSystem::QUARTER_COUNT;
        }

        return max(0, $free);
    }

    public function getBeamableTroopCount(ShipInterface $ship): int
    {
        $max = $ship->getCrewCount() - $ship->getBuildplan()->getCrew();

        return max(0, $max);
    }
}
