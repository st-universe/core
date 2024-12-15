<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Orm\Entity\SpacecraftInterface;

interface AlertDetectionInterface
{
    /**
     * @return array<int, AlertStateBattleParty>
     */
    public function detectAlertedBattleParties(
        SpacecraftInterface $incomingSpacecraft,
        InformationInterface $informations,
        ?SpacecraftInterface $tractoringSpacecraft = null
    ): array;
}
