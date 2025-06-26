<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertedBattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\IncomingBattleParty;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

interface AlertReactionFacadeInterface
{
    public function doItAll(
        SpacecraftWrapperInterface $incomingWrapper,
        InformationInterface $informations,
        ?Spacecraft $tractoringSpacecraft = null
    ): void;

    public function performAttackCycle(
        AlertedBattlePartyInterface $alertedParty,
        IncomingBattleParty $incomingParty,
        InformationWrapper $informations
    ): void;
}
