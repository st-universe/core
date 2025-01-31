<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ThreatReactionInterface
{
    public function reactToThreat(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        ShipInteractionEnum $interaction
    ): bool;
}
