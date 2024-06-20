<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\ShipInterface;

interface AlertedShipInformationInterface
{
    /** @param array<AlertStateBattleParty> $alertedBattleParties */
    public function addAlertedShipsInfo(
        ShipInterface $incomingShip,
        array $alertedBattleParties,
        InformationInterface $informations
    ): void;
}
