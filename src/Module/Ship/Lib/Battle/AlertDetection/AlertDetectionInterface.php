<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\ShipInterface;

interface AlertDetectionInterface
{
    /**
     * @return array<int, AlertStateBattleParty>
     */
    public function detectAlertedBattleParties(
        ShipInterface $incomingShip,
        InformationInterface $informations,
        ?ShipInterface $tractoringShip = null
    ): array;
}
