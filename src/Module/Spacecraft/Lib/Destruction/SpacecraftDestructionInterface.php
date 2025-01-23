<?php

namespace Stu\Module\Spacecraft\Lib\Destruction;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftDestructionInterface
{
    /**
     * Destroys a spacecraft and replaces it by a nice debrisfield,
     * also starts escape pods if present
     */
    public function destroy(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void;
}
