<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftAttackCoreInterface
{
    public function attack(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        bool &$isFleetFight,
        InformationWrapper $informations
    ): void;
}
