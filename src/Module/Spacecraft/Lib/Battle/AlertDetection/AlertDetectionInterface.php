<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\Spacecraft;

interface AlertDetectionInterface
{
    /**
     * @return array<int, AlertStateBattleParty>
     */
    public function detectAlertedBattleParties(
        Spacecraft $incomingSpacecraft,
        InformationInterface $informations,
        ?Spacecraft $tractoringSpacecraft = null
    ): array;
}
