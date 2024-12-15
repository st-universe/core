<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\SpacecraftInterface;

interface AlertedShipInformationInterface
{
    /** @param array<AlertStateBattleParty> $alertedBattleParties */
    public function addAlertedShipsInfo(
        SpacecraftInterface $incomingSpacecraft,
        array $alertedBattleParties,
        InformationInterface $informations
    ): void;
}
